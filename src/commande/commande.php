<?php

namespace App\Command;

use App\Entity\Continent;
use App\Entity\Langue;
use App\Entity\Pays;
use App\Repository\ContinentRepository;
use App\Repository\LangueRepository;
use App\Repository\PaysRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[AsCommand(
    name: 'app:import-pays',
    description: 'Importe (max 20) pays d\'un continent depuis l\'API /countries/v5',
)]
class ImportPaysCommand extends Command
{
    private const LIMIT = 100;       // taille de page
    private const MAX_PAYS = 20;     // max de pays pour le continent demandé
    private const FIELDS = [
        'names.common', 'codes.alpha_3', 'population', 'area.kilometers',
        'capitals', 'languages', 'continents', 'borders', 'flag.emoji',
    ];

    /** @var array<string, Pays> */
    private array $paysCache = [];
    /** @var array<string, Langue> */
    private array $langueCache = [];
    /** @var array<string, Continent> */
    private array $continentCache = [];

    public function __construct(
        private HttpClientInterface $client,
        private EntityManagerInterface $em,
        private ValidatorInterface $validator,
        private PaysRepository $paysRepository,
        private LangueRepository $langueRepository,
        private ContinentRepository $continentRepository,
        #[Autowire('%env(COUNTRIES_API_URL)%')] private string $apiUrl,
        #[Autowire('%env(COUNTRIES_API_KEY)%')] private string $apiKey,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('continent', InputArgument::REQUIRED, 'Ex : Europe');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $continentDemande = trim($input->getArgument('continent'));

        // ───── E : Extraire ─────
        $rows = $this->extract($continentDemande);
        $io->text(count($rows).' pays extraits pour '.$continentDemande);

        $frontieres = []; // codeAlpha3 => [codes voisins]
        $ignores = 0;

        foreach ($rows as $row) {
            // ───── T1 : Filtrer ─────
            $nom = trim((string) ($row['names']['common'] ?? ''));
            $code = strtoupper(trim((string) ($row['codes']['alpha_3'] ?? ''))); // T3
            if ($nom === '' || strlen($code) !== 3) {
                ++$ignores;
                continue;
            }

            // ───── T2 : Dédoublonner (par codeAlpha3) ─────
            $pays = $this->findPays($code) ?? new Pays();

            // ───── T3 : Normaliser ─────
            $pays->setNom($nom)
                ->setCodeAlpha3($code)
                ->setPopulation(is_numeric($row['population'] ?? null) ? (int) $row['population'] : null)
                ->setSuperficieKm2(is_numeric($row['area']['kilometers'] ?? null) ? (float) $row['area']['kilometers'] : null)
                // ───── T7 : capitale principale (null si aucune) ─────
                ->setCapitale($this->mainCapital($row['capitals'] ?? []));

            // ───── T4 : Relier les langues ─────
            foreach ($row['languages'] ?? [] as $l) {
                // ⚠ adapte 'name' / 'iso_639_3' au JSON réellement renvoyé
                $nomLangue = trim((string) ($l['name'] ?? ''));
                $codeIso = strtolower(trim((string) ($l['iso_639_3'] ?? '')));
                if ($nomLangue === '' || strlen($codeIso) !== 3) {
                    continue;
                }
                $pays->addLangue($this->getOrCreateLangue($nomLangue, $codeIso));
            }

            // ───── T5 : Relier les continents ─────
            foreach ($row['continents'] ?? [] as $c) {
                $nomContinent = trim((string) $c);
                if ($nomContinent === '') {
                    continue;
                }
                $pays->addContinent($this->getOrCreateContinent($nomContinent));
            }

            if (count($this->validator->validate($pays)) > 0) {
                ++$ignores;
                continue;
            }

            $this->em->persist($pays);
            $this->paysCache[$code] = $pays;

            $frontieres[$code] = array_map(
                fn ($b) => strtoupper(trim((string) $b)),
                $row['borders'] ?? []
            );
        }

        // ───── T6 : Relier les pays frontaliers (second passage) ─────
        foreach ($frontieres as $code => $codesVoisins) {
            $pays = $this->paysCache[$code];
            foreach ($codesVoisins as $codeVoisin) {
                $voisin = $this->findPays($codeVoisin);
                if ($voisin === null) {
                    continue; // voisin non importé : repris à un prochain import
                }
                // relation réciproque
                $pays->addPaysFrontalier($voisin);
                $voisin->addPaysFrontalier($pays);
            }
        }

        // Un seul flush à la fin
        $this->em->flush();

        $io->success(sprintf('%d pays traités, %d ignorés.', count($this->paysCache), $ignores));

        return Command::SUCCESS;
    }

    /** E : pagination limit/offset jusqu'à la fin, filtre sur continents[], max 20 pays */
    private function extract(string $continent): array
    {
        $result = [];
        $offset = 0;

        do {
            $response = $this->client->request('GET', rtrim($this->apiUrl, '/').'/countries/v5', [
                'headers' => ['Authorization' => 'Bearer '.$this->apiKey],
                'query' => [
                    'limit' => self::LIMIT,
                    'offset' => $offset,
                    'response_fields' => implode(',', self::FIELDS),
                ],
            ]);

            $objects = $response->toArray()['data']['objects'] ?? [];

            foreach ($objects as $object) {
                $continents = array_map(
                    fn ($c) => mb_strtolower(trim((string) $c)),
                    $object['continents'] ?? []
                );
                if (in_array(mb_strtolower($continent), $continents, true)) {
                    $result[] = $object;
                    if (count($result) >= self::MAX_PAYS) {
                        return $result;
                    }
                }
            }

            $offset += self::LIMIT;
        } while (count($objects) === self::LIMIT);

        return $result;
    }

    private function findPays(string $code): ?Pays
    {
        return $this->paysCache[$code]
            ??= $this->paysRepository->findOneBy(['codeAlpha3' => $code]);
    }

    private function getOrCreateLangue(string $nom, string $codeIso): Langue
    {
        $langue = $this->langueCache[$codeIso]
            ??= $this->langueRepository->findOneBy(['codeIso' => $codeIso]);

        if ($langue === null) {
            $langue = (new Langue())->setNom($nom)->setCodeIso($codeIso);
            $this->em->persist($langue);
            $this->langueCache[$codeIso] = $langue;
        }

        return $langue;
    }

    private function getOrCreateContinent(string $nom): Continent
    {
        $continent = $this->continentCache[$nom]
            ??= $this->continentRepository->findOneBy(['nom' => $nom]);

        if ($continent === null) {
            $continent = (new Continent())->setNom($nom);
            $this->em->persist($continent);
            $this->continentCache[$nom] = $continent;
        }

        return $continent;
    }

    private function mainCapital(array $capitals): ?string
    {
        foreach ($capitals as $capital) {
            $nom = trim((string) (is_array($capital) ? ($capital['name'] ?? '') : $capital));
            if ($nom !== '') {
                return $nom;
            }
        }

        return null;
    }
}
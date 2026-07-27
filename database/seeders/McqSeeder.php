<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeds a large, multi-sector bank of multiple-choice questions.
 *
 * All generation is DETERMINISTIC (no rand()/shuffle) so it can be produced
 * identically across repeated calls — this lets a batched web endpoint insert
 * the rows in slices on hosts with short max_execution_time (e.g. InfinityFree).
 *
 * MCQ columns: author_id, question, option_1..5, correct_answer_no (1-based).
 */
class McqSeeder extends Seeder
{
    public const AUTHOR_EMAIL = 'quiz-bank@quizapp.local';

    public function run(): void
    {
        $authorId = self::ensureAuthor();
        $rows = self::rows($authorId);

        // wipe previous bank rows for a clean, idempotent re-seed
        DB::table('m_c_q_s')->where('author_id', $authorId)->delete();

        foreach (array_chunk($rows, 500) as $chunk) {
            DB::table('m_c_q_s')->insert($chunk);
        }

        if ($this->command) {
            $this->command->info('Seeded '.count($rows).' MCQs (author_id='.$authorId.')');
        }
    }

    /** Ensure the dedicated content-author user exists; return its id. */
    public static function ensureAuthor(): int
    {
        $user = User::firstOrCreate(
            ['email' => self::AUTHOR_EMAIL],
            ['name' => 'Quiz Bank', 'password' => Hash::make('quiz-bank-'.bin2hex(random_bytes(6)))]
        );

        return (int) $user->id;
    }

    /**
     * Build the full deterministic list of MCQ rows.
     *
     * @return array<int,array<string,mixed>>
     */
    public static function rows(int $authorId): array
    {
        $now = now()->toDateTimeString();
        $out = [];

        $push = function (string $question, array $options, int $correct) use (&$out, $authorId, $now) {
            $options = array_values($options);
            $out[] = [
                'author_id' => $authorId,
                'question' => $question,
                'option_1' => (string) ($options[0] ?? ''),
                'option_2' => (string) ($options[1] ?? ''),
                'option_3' => isset($options[2]) ? (string) $options[2] : null,
                'option_4' => isset($options[3]) ? (string) $options[3] : null,
                'option_5' => isset($options[4]) ? (string) $options[4] : null,
                'correct_answer_no' => $correct,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        };

        // Deterministically place the correct answer among distractors and
        // return [options, correctNo]. $rot rotates the correct slot.
        $assemble = function (string $answer, array $distractors, int $rot): array {
            $distractors = array_values(array_slice($distractors, 0, 3));
            $slot = $rot % (count($distractors) + 1);      // 0..3
            $options = $distractors;
            array_splice($options, $slot, 0, [$answer]);   // insert correct at slot
            return [$options, $slot + 1];
        };

        // Pick N deterministic distractors from a pool, skipping the answer.
        $pick = function (array $pool, string $answer, int $seed, int $n = 3): array {
            $pool = array_values(array_unique(array_filter($pool, fn ($v) => $v !== $answer)));
            $count = count($pool);
            $picked = [];
            for ($k = 1; $k <= $n && $count > 0; $k++) {
                $picked[] = $pool[($seed * 7 + $k * 13) % $count];
            }
            return array_values(array_unique($picked));
        };

        // ---- Dataset: country => [capital, continent, currency] ----
        $countries = self::countries();
        $allCaps = array_map(fn ($v) => $v[0], $countries);
        $allCur = array_values(array_unique(array_map(fn ($v) => $v[2], $countries)));
        $continents = ['Africa', 'Asia', 'Europe', 'North America', 'South America', 'Oceania'];
        $i = 0;
        foreach ($countries as $country => [$capital, $continent, $currency]) {
            [$o, $c] = $assemble($capital, $pick($allCaps, $capital, $i), $i);
            $push("What is the capital of {$country}?", $o, $c);

            [$o, $c] = $assemble($continent, $pick($continents, $continent, $i + 1, 3), $i + 1);
            $push("On which continent is {$country} located?", $o, $c);

            [$o, $c] = $assemble($currency, $pick($allCur, $currency, $i + 2), $i + 2);
            $push("What is the official currency of {$country}?", $o, $c);
            $i++;
        }

        // ---- Dataset: chemical elements => [symbol, atomicNumber] ----
        $elements = self::elements();
        $allSym = array_map(fn ($v) => $v[0], $elements);
        $j = 0;
        foreach ($elements as $name => [$symbol, $z]) {
            [$o, $c] = $assemble($symbol, $pick($allSym, $symbol, $j), $j);
            $push("What is the chemical symbol for {$name}?", $o, $c);

            $nums = [$z, $z + 2, $z - 3, $z + 5];
            $nums = array_values(array_unique(array_map(fn ($n) => (string) max(1, $n), $nums)));
            [$o, $c] = $assemble((string) $z, array_values(array_filter($nums, fn ($n) => $n !== (string) $z)), $j + 1);
            $push("What is the atomic number of {$name}?", $o, $c);

            // reverse: which element has this symbol
            [$o, $c] = $assemble($name, $pick(array_keys($elements), $name, $j + 2), $j + 2);
            $push("Which element has the chemical symbol \"{$symbol}\"?", $o, $c);
            $j++;
        }

        // ---- Roman numerals ----
        $roman = self::romanMap();
        $rk = 0;
        foreach ($roman as $num => $rom) {
            [$o, $c] = $assemble($rom, $pick(array_values($roman), $rom, $rk), $rk);
            $push("What is {$num} in Roman numerals?", $o, $c);
            $rk++;
        }

        // ---- Math: multiplication tables (2..40) ----
        for ($a = 2; $a <= 40; $a++) {
            for ($b = 2; $b <= 40; $b++) {
                $ans = $a * $b;
                $d = [$ans + $a, $ans - $b, $ans + $a * $b > 0 ? $ans + 1 : $ans + 3];
                [$o, $c] = $assemble((string) $ans, array_map('strval', array_values(array_unique(array_filter($d, fn ($x) => $x !== $ans)))), $a + $b);
                $push("What is {$a} × {$b}?", $o, $c);
            }
        }

        // ---- Math: squares & cubes ----
        for ($n = 2; $n <= 60; $n++) {
            $ans = $n * $n;
            [$o, $c] = $assemble((string) $ans, [(string) ($ans + $n), (string) ($ans - $n), (string) ($ans + 2 * $n)], $n);
            $push("What is {$n} squared ({$n}²)?", $o, $c);
        }
        for ($n = 2; $n <= 20; $n++) {
            $ans = $n * $n * $n;
            [$o, $c] = $assemble((string) $ans, [(string) ($ans + $n), (string) ($ans - $n * $n), (string) ($ans + $n * $n)], $n + 1);
            $push("What is {$n} cubed ({$n}³)?", $o, $c);
        }

        // ---- Math: addition & subtraction ----
        for ($a = 13; $a <= 60; $a += 3) {
            for ($b = 17; $b <= 89; $b += 9) {
                $ans = $a + $b;
                [$o, $c] = $assemble((string) $ans, [(string) ($ans + 2), (string) ($ans - 3), (string) ($ans + 10)], $a + $b);
                $push("What is {$a} + {$b}?", $o, $c);
            }
        }
        for ($a = 120; $a <= 480; $a += 20) {
            $b = ($a % 97) + 11;
            $ans = $a - $b;
            [$o, $c] = $assemble((string) $ans, [(string) ($ans + 4), (string) ($ans - 6), (string) ($ans + 11)], $a + $b);
            $push("What is {$a} − {$b}?", $o, $c);
        }

        // ---- Math: percentages & division ----
        foreach ([[10, 250], [20, 150], [25, 480], [15, 300], [30, 90], [40, 200], [5, 640], [50, 74], [12, 500], [75, 160], [60, 45], [8, 250]] as $k => [$p, $y]) {
            $ans = (int) round($p * $y / 100);
            [$o, $c] = $assemble((string) $ans, [(string) ($ans + $p), (string) ($ans - 5), (string) ($ans * 2)], $k);
            $push("What is {$p}% of {$y}?", $o, $c);
        }
        for ($a = 2; $a <= 25; $a++) {
            $ans = $a;
            $dividend = $a * (($a % 9) + 2);
            $divisor = ($a % 9) + 2;
            [$o, $c] = $assemble((string) $ans, [(string) ($ans + 1), (string) ($ans + 2), (string) (max(1, $ans - 1))], $a);
            $push("What is {$dividend} ÷ {$divisor}?", $o, $c);
        }

        // ---- Curated multi-sector bank ----
        foreach (self::curated() as [$q, $opts, $correct]) {
            $push($q, $opts, $correct);
        }

        return $out;
    }

    /** country => [capital, continent, currency] */
    private static function countries(): array
    {
        return [
            'France' => ['Paris', 'Europe', 'Euro'],
            'Germany' => ['Berlin', 'Europe', 'Euro'],
            'Italy' => ['Rome', 'Europe', 'Euro'],
            'Spain' => ['Madrid', 'Europe', 'Euro'],
            'Portugal' => ['Lisbon', 'Europe', 'Euro'],
            'United Kingdom' => ['London', 'Europe', 'Pound Sterling'],
            'Ireland' => ['Dublin', 'Europe', 'Euro'],
            'Netherlands' => ['Amsterdam', 'Europe', 'Euro'],
            'Belgium' => ['Brussels', 'Europe', 'Euro'],
            'Switzerland' => ['Bern', 'Europe', 'Swiss Franc'],
            'Austria' => ['Vienna', 'Europe', 'Euro'],
            'Sweden' => ['Stockholm', 'Europe', 'Swedish Krona'],
            'Norway' => ['Oslo', 'Europe', 'Norwegian Krone'],
            'Denmark' => ['Copenhagen', 'Europe', 'Danish Krone'],
            'Finland' => ['Helsinki', 'Europe', 'Euro'],
            'Poland' => ['Warsaw', 'Europe', 'Zloty'],
            'Greece' => ['Athens', 'Europe', 'Euro'],
            'Russia' => ['Moscow', 'Europe', 'Russian Ruble'],
            'Ukraine' => ['Kyiv', 'Europe', 'Hryvnia'],
            'Czech Republic' => ['Prague', 'Europe', 'Czech Koruna'],
            'Hungary' => ['Budapest', 'Europe', 'Forint'],
            'Romania' => ['Bucharest', 'Europe', 'Romanian Leu'],
            'China' => ['Beijing', 'Asia', 'Renminbi'],
            'Japan' => ['Tokyo', 'Asia', 'Japanese Yen'],
            'India' => ['New Delhi', 'Asia', 'Indian Rupee'],
            'South Korea' => ['Seoul', 'Asia', 'South Korean Won'],
            'Indonesia' => ['Jakarta', 'Asia', 'Rupiah'],
            'Thailand' => ['Bangkok', 'Asia', 'Thai Baht'],
            'Vietnam' => ['Hanoi', 'Asia', 'Vietnamese Dong'],
            'Malaysia' => ['Kuala Lumpur', 'Asia', 'Ringgit'],
            'Singapore' => ['Singapore', 'Asia', 'Singapore Dollar'],
            'Philippines' => ['Manila', 'Asia', 'Philippine Peso'],
            'Pakistan' => ['Islamabad', 'Asia', 'Pakistani Rupee'],
            'Bangladesh' => ['Dhaka', 'Asia', 'Bangladeshi Taka'],
            'Sri Lanka' => ['Colombo', 'Asia', 'Sri Lankan Rupee'],
            'Nepal' => ['Kathmandu', 'Asia', 'Nepalese Rupee'],
            'Saudi Arabia' => ['Riyadh', 'Asia', 'Saudi Riyal'],
            'United Arab Emirates' => ['Abu Dhabi', 'Asia', 'UAE Dirham'],
            'Turkey' => ['Ankara', 'Asia', 'Turkish Lira'],
            'Iran' => ['Tehran', 'Asia', 'Iranian Rial'],
            'Iraq' => ['Baghdad', 'Asia', 'Iraqi Dinar'],
            'Israel' => ['Jerusalem', 'Asia', 'Israeli Shekel'],
            'Egypt' => ['Cairo', 'Africa', 'Egyptian Pound'],
            'Nigeria' => ['Abuja', 'Africa', 'Naira'],
            'South Africa' => ['Pretoria', 'Africa', 'South African Rand'],
            'Kenya' => ['Nairobi', 'Africa', 'Kenyan Shilling'],
            'Ethiopia' => ['Addis Ababa', 'Africa', 'Ethiopian Birr'],
            'Ghana' => ['Accra', 'Africa', 'Ghanaian Cedi'],
            'Morocco' => ['Rabat', 'Africa', 'Moroccan Dirham'],
            'Algeria' => ['Algiers', 'Africa', 'Algerian Dinar'],
            'Tanzania' => ['Dodoma', 'Africa', 'Tanzanian Shilling'],
            'Uganda' => ['Kampala', 'Africa', 'Ugandan Shilling'],
            'United States' => ['Washington, D.C.', 'North America', 'US Dollar'],
            'Canada' => ['Ottawa', 'North America', 'Canadian Dollar'],
            'Mexico' => ['Mexico City', 'North America', 'Mexican Peso'],
            'Cuba' => ['Havana', 'North America', 'Cuban Peso'],
            'Guatemala' => ['Guatemala City', 'North America', 'Quetzal'],
            'Jamaica' => ['Kingston', 'North America', 'Jamaican Dollar'],
            'Panama' => ['Panama City', 'North America', 'Balboa'],
            'Brazil' => ['Brasília', 'South America', 'Brazilian Real'],
            'Argentina' => ['Buenos Aires', 'South America', 'Argentine Peso'],
            'Chile' => ['Santiago', 'South America', 'Chilean Peso'],
            'Colombia' => ['Bogotá', 'South America', 'Colombian Peso'],
            'Peru' => ['Lima', 'South America', 'Sol'],
            'Venezuela' => ['Caracas', 'South America', 'Bolívar'],
            'Ecuador' => ['Quito', 'South America', 'US Dollar'],
            'Bolivia' => ['Sucre', 'South America', 'Boliviano'],
            'Uruguay' => ['Montevideo', 'South America', 'Uruguayan Peso'],
            'Paraguay' => ['Asunción', 'South America', 'Guaraní'],
            'Australia' => ['Canberra', 'Oceania', 'Australian Dollar'],
            'New Zealand' => ['Wellington', 'Oceania', 'New Zealand Dollar'],
            'Fiji' => ['Suva', 'Oceania', 'Fijian Dollar'],
            'Papua New Guinea' => ['Port Moresby', 'Oceania', 'Kina'],
        ];
    }

    /** element name => [symbol, atomic number] */
    private static function elements(): array
    {
        return [
            'Hydrogen' => ['H', 1], 'Helium' => ['He', 2], 'Lithium' => ['Li', 3],
            'Beryllium' => ['Be', 4], 'Boron' => ['B', 5], 'Carbon' => ['C', 6],
            'Nitrogen' => ['N', 7], 'Oxygen' => ['O', 8], 'Fluorine' => ['F', 9],
            'Neon' => ['Ne', 10], 'Sodium' => ['Na', 11], 'Magnesium' => ['Mg', 12],
            'Aluminium' => ['Al', 13], 'Silicon' => ['Si', 14], 'Phosphorus' => ['P', 15],
            'Sulfur' => ['S', 16], 'Chlorine' => ['Cl', 17], 'Argon' => ['Ar', 18],
            'Potassium' => ['K', 19], 'Calcium' => ['Ca', 20], 'Iron' => ['Fe', 26],
            'Cobalt' => ['Co', 27], 'Nickel' => ['Ni', 28], 'Copper' => ['Cu', 29],
            'Zinc' => ['Zn', 30], 'Silver' => ['Ag', 47], 'Tin' => ['Sn', 50],
            'Iodine' => ['I', 53], 'Gold' => ['Au', 79], 'Mercury' => ['Hg', 80],
            'Lead' => ['Pb', 82], 'Uranium' => ['U', 92], 'Titanium' => ['Ti', 22],
            'Chromium' => ['Cr', 24], 'Manganese' => ['Mn', 25], 'Platinum' => ['Pt', 78],
            'Barium' => ['Ba', 56], 'Bromine' => ['Br', 35], 'Krypton' => ['Kr', 36],
            'Xenon' => ['Xe', 54], 'Neodymium' => ['Nd', 60], 'Tungsten' => ['W', 74],
            'Arsenic' => ['As', 33], 'Selenium' => ['Se', 34], 'Radium' => ['Ra', 88],
            'Plutonium' => ['Pu', 94], 'Germanium' => ['Ge', 32], 'Gallium' => ['Ga', 31],
            'Cadmium' => ['Cd', 48], 'Antimony' => ['Sb', 51], 'Cesium' => ['Cs', 55],
            'Palladium' => ['Pd', 46], 'Rhodium' => ['Rh', 45], 'Zirconium' => ['Zr', 40],
        ];
    }

    /** number => roman numeral (deterministic sample) */
    private static function romanMap(): array
    {
        $to = function (int $n): string {
            $map = [1000 => 'M', 900 => 'CM', 500 => 'D', 400 => 'CD', 100 => 'C', 90 => 'XC', 50 => 'L', 40 => 'XL', 10 => 'X', 9 => 'IX', 5 => 'V', 4 => 'IV', 1 => 'I'];
            $r = '';
            foreach ($map as $v => $sym) {
                while ($n >= $v) { $r .= $sym; $n -= $v; }
            }
            return $r;
        };
        $out = [];
        foreach ([1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 14, 16, 18, 19, 21, 24, 27, 29, 33, 36, 39, 42, 44, 46, 48, 49, 51, 54, 58, 63, 67, 72, 76, 79, 84, 88, 90, 91, 94, 96, 99, 100, 400, 500, 900, 1000, 1500, 1990, 2024] as $n) {
            $out[$n] = $to($n);
        }
        return $out;
    }

    /** [question, options[], correctNo] — accurate, hand-curated, multi-sector. */
    private static function curated(): array
    {
        return [
            // Programming / IT
            ['Which language is primarily used to style web pages?', ['HTML', 'CSS', 'SQL', 'PHP'], 2],
            ['What does "HTTP" stand for?', ['HyperText Transfer Protocol', 'High Transfer Text Protocol', 'Hyperlink Transfer Process', 'Host Transfer Type Protocol'], 1],
            ['Which data structure uses FIFO order?', ['Stack', 'Queue', 'Tree', 'Graph'], 2],
            ['Which of these is a NoSQL database?', ['PostgreSQL', 'MySQL', 'MongoDB', 'SQLite'], 3],
            ['In Git, which command creates a new branch?', ['git branch', 'git commit', 'git pull', 'git merge'], 1],
            ['What does "CPU" stand for?', ['Central Processing Unit', 'Computer Personal Unit', 'Central Print Utility', 'Control Process Unit'], 1],
            ['Which language runs natively in web browsers?', ['Python', 'JavaScript', 'Ruby', 'Go'], 2],
            ['What is the base of the binary number system?', ['2', '8', '10', '16'], 1],
            ['Which HTTP status code means "Not Found"?', ['200', '301', '404', '500'], 3],
            ['Which company created the Java programming language?', ['Microsoft', 'Sun Microsystems', 'Apple', 'IBM'], 2],
            ['What does "SQL" stand for?', ['Structured Query Language', 'Simple Query Language', 'Sequential Query Logic', 'Standard Question Language'], 1],
            ['Which of these is a version control system?', ['Docker', 'Git', 'Kubernetes', 'Jenkins'], 2],
            ['What symbol starts a variable in PHP?', ['#', '@', '$', '&'], 3],
            ['Which protocol is used to send email?', ['SMTP', 'FTP', 'HTTP', 'SSH'], 1],
            ['What does "RAM" stand for?', ['Random Access Memory', 'Read Access Memory', 'Rapid Access Module', 'Runtime Address Map'], 1],

            // World History
            ['In which year did World War II end?', ['1943', '1945', '1918', '1950'], 2],
            ['Who was the first President of the United States?', ['Thomas Jefferson', 'Abraham Lincoln', 'George Washington', 'John Adams'], 3],
            ['The Great Wall is located in which country?', ['India', 'China', 'Japan', 'Mongolia'], 2],
            ['Who painted the Mona Lisa?', ['Michelangelo', 'Leonardo da Vinci', 'Raphael', 'Donatello'], 2],
            ['In which year did the Titanic sink?', ['1905', '1912', '1920', '1898'], 2],
            ['Which ancient civilization built the pyramids of Giza?', ['Romans', 'Greeks', 'Egyptians', 'Persians'], 3],
            ['Who discovered gravity after (reportedly) seeing an apple fall?', ['Einstein', 'Newton', 'Galileo', 'Darwin'], 2],
            ['The French Revolution began in which year?', ['1776', '1789', '1804', '1815'], 2],
            ['Who was the leader of Nazi Germany during WWII?', ['Mussolini', 'Stalin', 'Hitler', 'Franco'], 3],
            ['Which empire was ruled by Julius Caesar?', ['Greek', 'Roman', 'Ottoman', 'Persian'], 2],

            // Science / Physics / Biology
            ['What planet is known as the Red Planet?', ['Venus', 'Mars', 'Jupiter', 'Saturn'], 2],
            ['What gas do plants absorb from the atmosphere?', ['Oxygen', 'Nitrogen', 'Carbon dioxide', 'Hydrogen'], 3],
            ['How many bones are in the adult human body?', ['206', '201', '210', '196'], 1],
            ['What is the powerhouse of the cell?', ['Nucleus', 'Ribosome', 'Mitochondria', 'Golgi body'], 3],
            ['What is the speed of light (approx.)?', ['300,000 km/s', '150,000 km/s', '1,000 km/s', '3,000 km/s'], 1],
            ['Which blood cells fight infection?', ['Red blood cells', 'White blood cells', 'Platelets', 'Plasma'], 2],
            ['What force keeps planets in orbit around the Sun?', ['Magnetism', 'Gravity', 'Friction', 'Tension'], 2],
            ['Water is made of hydrogen and which other element?', ['Carbon', 'Oxygen', 'Nitrogen', 'Helium'], 2],
            ['What is the largest organ of the human body?', ['Liver', 'Brain', 'Skin', 'Lungs'], 3],
            ['Which planet is the largest in our solar system?', ['Earth', 'Saturn', 'Jupiter', 'Neptune'], 3],
            ['What is the hardest known natural material?', ['Gold', 'Iron', 'Diamond', 'Quartz'], 3],
            ['Which vitamin is produced when skin is exposed to sunlight?', ['Vitamin A', 'Vitamin C', 'Vitamin D', 'Vitamin K'], 3],
            ['How many planets are in our solar system?', ['7', '8', '9', '10'], 2],
            ['What is the chemical formula for water?', ['CO2', 'H2O', 'O2', 'NaCl'], 2],
            ['Sound travels fastest through which medium?', ['Vacuum', 'Air', 'Water', 'Steel'], 4],

            // Geography (non-capital)
            ['Which is the longest river in the world?', ['Amazon', 'Nile', 'Yangtze', 'Mississippi'], 2],
            ['Which is the largest ocean on Earth?', ['Atlantic', 'Indian', 'Arctic', 'Pacific'], 4],
            ['Mount Everest is located in which mountain range?', ['Andes', 'Alps', 'Himalayas', 'Rockies'], 3],
            ['Which desert is the largest hot desert in the world?', ['Gobi', 'Sahara', 'Kalahari', 'Arabian'], 2],
            ['Which country has the largest population?', ['India', 'China', 'USA', 'Indonesia'], 1],
            ['The Great Barrier Reef is off the coast of which country?', ['Brazil', 'Australia', 'Mexico', 'Thailand'], 2],
            ['Which continent is the largest by area?', ['Africa', 'Asia', 'Europe', 'North America'], 2],
            ['Which country is both in Europe and Asia?', ['Egypt', 'Turkey', 'Greece', 'Italy'], 2],

            // Sports
            ['How many players are on a football (soccer) team on the field?', ['9', '10', '11', '12'], 3],
            ['In which sport would you perform a "slam dunk"?', ['Tennis', 'Basketball', 'Golf', 'Cricket'], 2],
            ['How often are the Summer Olympic Games held?', ['Every 2 years', 'Every 3 years', 'Every 4 years', 'Every 5 years'], 3],
            ['Which country has won the most FIFA World Cups?', ['Germany', 'Italy', 'Brazil', 'Argentina'], 3],
            ['In tennis, what is a score of zero called?', ['Nil', 'Love', 'Duck', 'Blank'], 2],
            ['How many rings are on the Olympic flag?', ['4', '5', '6', '7'], 2],
            ['In cricket, how many players are in a team?', ['9', '10', '11', '12'], 3],

            // English / Language
            ['What is the plural of "mouse" (the animal)?', ['Mouses', 'Mice', 'Meese', 'Mouse'], 2],
            ['Which word is a synonym of "happy"?', ['Sad', 'Joyful', 'Angry', 'Tired'], 2],
            ['Which word is an antonym of "begin"?', ['Start', 'Commence', 'End', 'Open'], 3],
            ['How many letters are in the English alphabet?', ['24', '25', '26', '27'], 3],
            ['Which of these is a preposition?', ['Quickly', 'Under', 'Happy', 'Run'], 2],
            ['What is the past tense of "go"?', ['Goed', 'Gone', 'Went', 'Goes'], 3],

            // General knowledge / Arts / Economics
            ['How many continents are there on Earth?', ['5', '6', '7', '8'], 3],
            ['What is the currency used in Japan?', ['Yuan', 'Won', 'Yen', 'Ringgit'], 3],
            ['Who wrote the play "Romeo and Juliet"?', ['Charles Dickens', 'William Shakespeare', 'Mark Twain', 'Jane Austen'], 2],
            ['Which instrument has 88 keys?', ['Guitar', 'Violin', 'Piano', 'Flute'], 3],
            ['How many colors are in a rainbow?', ['5', '6', '7', '8'], 3],
            ['What does "GDP" stand for in economics?', ['Gross Domestic Product', 'General Domestic Price', 'Gross Data Product', 'Global Demand Price'], 1],
            ['Which planet is closest to the Sun?', ['Venus', 'Mercury', 'Earth', 'Mars'], 2],
            ['How many minutes are in a full day?', ['1200', '1440', '1600', '2400'], 2],
            ['What is the freezing point of water in Celsius?', ['-10°C', '0°C', '10°C', '32°C'], 2],
            ['Which is the smallest prime number?', ['0', '1', '2', '3'], 3],
        ];
    }
}

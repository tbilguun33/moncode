<?php

namespace Database\Seeders;

use App\Enums\EditorType;
use App\Enums\ReactionType;
use App\Models\Course;
use App\Models\ProgressTracker;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const VIDEO_URL = 'https://www.youtube.com/embed/dQw4w9WgXcQ';

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@example.com',
        ]);

        $reviewer = User::factory()->create([
            'name' => 'Sarah K.',
            'email' => 'sarah@example.com',
        ]);

        $courses = $this->courseDefinitions();
        $completedLessons = [];

        foreach ($courses as $order => $courseData) {
            $course = Course::create([
                'name' => $courseData['name'],
                'slug' => $courseData['slug'],
                'description' => $courseData['description'],
                'icon' => $courseData['icon'],
                'editor_type' => $courseData['editor_type'],
                'order' => $order + 1,
            ]);

            // Text-based courses (HTML/CSS/C++) are read as documentation;
            // only the video-based course (Unity) carries a video_url.
            $isVideoBased = $courseData['editor_type'] === null;

            foreach ($courseData['categories'] as $catOrder => $categoryData) {
                $category = $course->categories()->create([
                    'name' => $categoryData['name'],
                    'slug' => $categoryData['slug'],
                    'description' => $categoryData['description'],
                    'order' => $catOrder + 1,
                ]);

                foreach ($categoryData['lessons'] as $lessonOrder => $lessonData) {
                    $lesson = $category->lessons()->create([
                        'title' => $lessonData['title'],
                        'slug' => $lessonData['slug'],
                        'video_url' => $isVideoBased ? self::VIDEO_URL : null,
                        'content' => $lessonData['content'],
                        'starter_code' => $lessonData['starter'],
                        'order' => $lessonOrder + 1,
                    ]);

                    if (! $isVideoBased) {
                        $completedLessons[] = $lesson;
                    }
                }
            }
        }

        foreach ($completedLessons as $lesson) {
            ProgressTracker::create([
                'user_id' => $user->id,
                'lesson_id' => $lesson->id,
                'completed' => true,
                'completed_at' => now(),
            ]);
        }

        $project = $user->projects()->create([
            'title' => 'Миний анхны 2D платформер тоглоом',
            'description' => 'Unity дээр хийсэн анхны 2D платформер тоглоом. Player movement, үсрэлт, болон энгийн enemy AI орсон болно.',
        ]);

        foreach ([['Screenshot 1', '2563eb'], ['Screenshot 2', '16a34a'], ['Screenshot 3', 'd97706']] as $index => [$label, $hex]) {
            $project->images()->create([
                'path' => $this->placeholderImage($label, $hex),
                'order' => $index,
            ]);
        }

        $project->likes()->create(['user_id' => $reviewer->id, 'type' => ReactionType::Like]);
        $project->comments()->create(['user_id' => $reviewer->id, 'body' => 'Гоё байна! Physics санагдмаар сайхан ажиллаж байна.']);
        $project->comments()->create(['user_id' => $user->id, 'body' => 'Баярлалаа! Дараагийн шинэчлэлээр audio нэмнэ.']);
    }

    /**
     * @return array<int, array{name: string, slug: string, description: string, icon: string, editor_type: ?EditorType, categories: array<int, array{name: string, slug: string, description: string, lessons: array<int, array{title: string, slug: string, content: string, starter: ?string}>}>}>
     */
    private function courseDefinitions(): array
    {
        $htmlStarter = fn (string $body) => <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
        <style>
        h1 { color: #1d4ed8; }
        </style>
        </head>
        <body>
        {$body}
        </body>
        </html>
        HTML;

        $cssStarter = <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
        <style>
        h1 { color: #1d4ed8; text-align: center; }
        p { font-family: verdana; font-size: 18px; }
        </style>
        </head>
        <body>
        <h1>CSS жишээ</h1>
        <p>Энэ бол параграф.</p>
        </body>
        </html>
        HTML;

        $cssPageStarter = fn (string $css, string $body) => <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
        <style>
        {$css}
        </style>
        </head>
        <body>
        {$body}
        </body>
        </html>
        HTML;

        return [
            [
                'name' => 'HTML',
                'slug' => 'html',
                'icon' => 'html',
                'editor_type' => EditorType::Web,
                'description' => 'HTML Веб хөгжүүлэлтийн иж бүрэн сурах бичиг — анхан шатнаас гүнзгий түвшин хүртэл 25 бүлэгт.',
                'categories' => $this->htmlBookCategories($htmlStarter, $cssStarter),
            ],
            [
                'name' => 'CSS',
                'slug' => 'css',
                'icon' => 'css',
                'editor_type' => EditorType::Web,
                'description' => 'CSS Анхан Шатны Бүрэн Гарын Авлага — веб сайт хөгжүүлэлтийн үндэс, 25 сэдэвт.',
                'categories' => $this->cssBookCategories($cssPageStarter),
            ],
            [
                'name' => 'C++',
                'slug' => 'cpp',
                'icon' => 'cpp',
                'editor_type' => EditorType::Cpp,
                'description' => 'C++ Програмчлалын Бүрэн Сурах Бичиг — суурь ойлголтоос OOP хүртэл 6 бүлэгт, 24 хичээл.',
                'categories' => $this->cppBookCategories(),
            ],
            [
                'name' => 'Unity',
                'slug' => 'unity',
                'icon' => 'unity',
                'editor_type' => null,
                'description' => '2D/3D тоглоом хөгжүүлэх Unity engine болон C# scripting-ийн үндэс.',
                'categories' => [
                    [
                        'name' => 'Тоглоомын үндэс',
                        'slug' => 'basics',
                        'description' => 'Unity editor, Scene, GameObject зэрэг үндсэн ойлголтууд.',
                        'lessons' => [
                            ['title' => 'Unity танилцуулга', 'slug' => 'intro', 'content' => 'Unity editor-ийн үндсэн цонхнуудтай танилцъя.', 'starter' => null],
                            ['title' => 'Scene ба GameObject', 'slug' => 'scene-gameobject', 'content' => 'Scene дотор GameObject үүсгэж, Component нэмэх нь.', 'starter' => null],
                            ['title' => 'C# scripting эхлэл', 'slug' => 'csharp-scripting', 'content' => 'MonoBehaviour скрипт бичиж GameObject-той холбох нь.', 'starter' => null],
                        ],
                    ],
                ],
            ],
        ];
    }

    /**
     * The 25-chapter HTML curriculum, adapted from "HTML Веб хөгжүүлэлт" —
     * one category per chapter, one comprehensive lesson per category.
     *
     * @return array<int, array{name: string, slug: string, description: string, lessons: array<int, array{title: string, slug: string, content: string, starter: ?string}>}>
     */
    private function htmlBookCategories(\Closure $htmlStarter, string $cssStarter): array
    {
        $chapter = fn (string $title, string $slug, string $description, string $content, ?string $starter) => [
            'name' => $title,
            'slug' => $slug,
            'description' => $description,
            'lessons' => [[
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'starter' => $starter,
            ]],
        ];

        return [
            $chapter(
                'HTML Оршил',
                'introduction',
                'Веб сайт хэрхэн бүтдэг вэ, HTML гэж юу вэ.',
                <<<'HTMLCONTENT'
                <p>Бидний өдөр тутам ашигладаг интернэт дэх аливаа веб хуудас нь HTML гэх стандартын дагуу бүтээгддэг. Амьдрал дээрх барилгатай харьцуулбал <strong>HTML</strong> нь барилгын каркас буюу тулгуур яс, <strong>CSS</strong> нь түүний будаг, дизайн (хувцас), <strong>JavaScript</strong> нь цахилгаан, сантехникийн хөдөлгөөнт систем (ухаалаг ажиллагаа) юм.</p>
                <h3>HTML гэж юу вэ?</h3>
                <ul>
                    <li>HTML гэдэг нь <strong>HyperText Markup Language</strong> гэсэн үгсийн товчлол.</li>
                    <li>HTML нь програмчлалын хэл БИШ, харин тэмдэглэгээт хэл (markup language) юм — тусгай tag ашиглан веб хуудасны бүтцийг тодорхойлдог.</li>
                    <li>HTML нь веб хөтөчид (Chrome, Firefox, Safari г.м) текст, зураг, хүснэгтийг хэрхэн харуулах ёстойг зааж өгдөг.</li>
                </ul>
                <h3>Энгийн HTML баримтын бүтэц</h3>
                <p>Аливаа HTML хуудас дараах үндсэн бүтцийг заавал агуулна: <code>&lt;!DOCTYPE html&gt;</code>, <code>&lt;html&gt;</code>, <code>&lt;head&gt;</code>, <code>&lt;body&gt;</code>. Эдгээрийг дараагийн хичээлүүдээр дэлгэрэнгүй үзнэ.</p>
                HTMLCONTENT,
                $htmlStarter("<h1>Миний анхны гарчиг</h1>\n<p>Миний анхны эх бэлтгэл буюу параграф бичвэр.</p>"),
            ),
            $chapter(
                'HTML Код редакторын тухай',
                'editors',
                'Код бичиж эхлэхэд ямар хэрэгсэл хэрэгтэй вэ.',
                <<<'HTMLCONTENT'
                <p>Код бичиж эхлэхийн тулд танд ямар нэгэн үнэтэй програм, тусгай тоног төхөөрөмж шаардлагагүй. Компьютерт байдаг энгийн текст боловсруулах програм ч хангалттай.</p>
                <h3>Анхны кодоо хэрхэн бичих вэ?</h3>
                <ol>
                    <li><strong>Текст редакторыг нээх:</strong> Windows дээр Notepad, Mac дээр TextEdit (Format → Make Plain Text сонголтоор).</li>
                    <li><strong>Кодоо бичих:</strong> Доорх жишээ кодыг яг хэвээр нь бичнэ.</li>
                    <li><strong>Файлаа хадгалах:</strong> File → Save As, файлын нэрийг <code>index.html</code> гэж өгнө (заавал <code>.html</code> өргөтгөлтэй). Кодчилол (encoding) хэсэгт <strong>UTF-8</strong> сонговол Монгол үсэг зөв харагдана.</li>
                    <li><strong>Браузер дээр нээж үзэх:</strong> Хадгалсан <code>index.html</code> файл дээрээ хоёр дахин дарахад веб хуудас гарч ирнэ.</li>
                </ol>
                <h3>Мэргэжлийн редакторууд</h3>
                <p>Цаашид илүү том төсөл дээр ажиллахад зориулсан, кодын өнгө ялгадаг, автоматаар гүйцээдэг үнэгүй хэрэгслүүд:</p>
                <ul>
                    <li><strong>Visual Studio Code (VS Code)</strong> — орчин үеийн хамгийн алдартай редактор.</li>
                    <li><strong>Sublime Text</strong> — хөнгөн, хурдан ажиллагаатай редактор.</li>
                    <li><strong>Notepad++</strong> — Windows системд зориулсан энгийн редактор.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<h1>Сайн байна уу, Дэлхий!</h1>\n<p>Би анхны веб хуудсаа амжилттай үүсгэлээ.</p>"),
            ),
            $chapter(
                'HTML Анхны үндсэн ойлголт',
                'basic',
                'Гарчиг, параграф, холбоос, зурагтай анхны танилцал.',
                <<<'HTMLCONTENT'
                <p>Энэ хэсэгт HTML хуудасны хамгийн өргөн хэрэглэгддэг үндсэн элементүүдтэй товч танилцана — дараагийн хичээлүүдэд эдгээрийг тус бүрчлэн дэлгэрэнгүй үзнэ.</p>
                <h3>HTML гарчиг (Headings)</h3>
                <p>Гарчгийг <code>&lt;h1&gt;</code>-ээс <code>&lt;h6&gt;</code> хүртэлх tag-аар тодорхойлно. <code>&lt;h1&gt;</code> нь хамгийн том, хамгийн чухал гарчиг юм.</p>
                <h3>HTML параграф (Paragraphs)</h3>
                <p>Эх бичвэрийг <code>&lt;p&gt;</code> tag-аар бичнэ: <code>&lt;p&gt;Энэ бол энгийн бичвэр параграф юм.&lt;/p&gt;</code></p>
                <h3>HTML холбоос (Links)</h3>
                <p>Веб хуудас хооронд шилжих холбоосыг <code>&lt;a&gt;</code> tag болон <code>href</code> аттрибутаар үүсгэнэ: <code>&lt;a href="https://www.google.com"&gt;Google рүү зочлох&lt;/a&gt;</code></p>
                <h3>HTML зураг (Images)</h3>
                <p>Зургийг <code>&lt;img&gt;</code> tag-аар оруулдаг. Зургийн файлын замыг <code>src</code>, тайлбарыг <code>alt</code>, хэмжээг <code>width</code> ба <code>height</code>-ээр заана: <code>&lt;img src="logo.jpg" alt="Лого зураг" width="200" height="100"&gt;</code></p>
                HTMLCONTENT,
                $htmlStarter("<h1>1-р зэргийн гарчиг</h1>\n<p>Энэ бол энгийн бичвэр параграф юм.</p>\n<a href=\"https://www.google.com\">Google рүү зочлох</a>"),
            ),
            $chapter(
                'HTML Элементүүд',
                'elements',
                'Эхлэх tag, агуулга, төгсгөх tag, доторлосон болон хоосон элементүүд.',
                <<<'HTMLCONTENT'
                <p>HTML баримт бичиг нь олон тооны HTML элементүүдээс бүрддэг.</p>
                <h3>HTML элемент гэж юу вэ?</h3>
                <p>HTML элемент нь <strong>эхлэх tag (start tag)</strong>, <strong>агуулга (content)</strong>, болон <strong>төгсгөлийн tag (end tag)</strong>-ээс бүрдэнэ: <code>&lt;h1&gt;Миний үндсэн гарчиг&lt;/h1&gt;</code>. <code>&lt;br&gt;</code> зэрэг зарим tag агуулгагүй, хаалтын tag шаардаггүй.</p>
                <h3>Доторлосон элементүүд (Nested Elements)</h3>
                <p>HTML элементүүд нь бие биенийхээ дотор агуулагдаж болно. Дараах жишээнд <code>&lt;b&gt;</code> элемент нь <code>&lt;p&gt;</code> дотор, <code>&lt;p&gt;</code> нь <code>&lt;body&gt;</code> дотор, <code>&lt;body&gt;</code> нь <code>&lt;html&gt;</code> дотор агуулагдаж байна.</p>
                <h3>Хоосон HTML элементүүд (Empty Elements)</h3>
                <p>Агуулга агуулдаггүй элементүүдийг хоосон элемент гэнэ. <code>&lt;br&gt;</code> (шинэ мөрөөр шилжүүлэх) болон <code>&lt;hr&gt;</code> (хэвтээ зураас) нь хаалтын tag шаарддаггүй хоосон элементүүд юм.</p>
                <p><strong>Анхаарах зүйл:</strong> Төгсгөлийн tag-ийг хаахаа хэзээ ч бүү мартаарай! Зарим браузер хаалтгүй tag-ийг алдаагүй мэт харуулж болох ч дараа дараагийн элементүүдийн дизайныг эвдэх аюултай.</p>
                HTMLCONTENT,
                $htmlStarter("<h1>Миний гарчиг</h1>\n<p>Миний <b>тод</b> үгтэй параграф.</p>\n<hr>\n<p>Хэвтээ зурасны доорх текст.</p>"),
            ),
            $chapter(
                'HTML Аттрибутууд',
                'attributes',
                'Элементэд нэмэлт мэдээлэл өгөх нэр="утга" хосууд.',
                <<<'HTMLCONTENT'
                <p>Аттрибут нь HTML элементэд нэмэлт мэдээлэл болон тохиргоо олгодог боломж юм.</p>
                <h3>Аттрибутын үндсэн дүрмүүд</h3>
                <ul>
                    <li>Бүх HTML элементүүд аттрибуттай байж болно.</li>
                    <li>Аттрибутыг заавал эхлэх tag (start tag) дотор бичнэ.</li>
                    <li>Аттрибут нь ихэвчлэн <code>нэр="утга"</code> (<code>name="value"</code>) гэсэн хослолоор бичигддэг ба утгыг заавал хашилтад авна.</li>
                </ul>
                <h3>Чухал аттрибутуудын жагсаалт</h3>
                <ul>
                    <li><code>href</code> — холбоос линкийн шилжих веб хаягийг заана: <code>&lt;a href="https://site.mn"&gt;</code></li>
                    <li><code>src</code> — зургийн файлын замыг заана: <code>&lt;img src="pic.jpg"&gt;</code></li>
                    <li><code>width</code>, <code>height</code> — зургийн өргөн ба өндрийг пикселээр заана</li>
                    <li><code>alt</code> — зураг ачаалагдахгүй үед харагдах орлуулах текст</li>
                    <li><code>style</code> — элементэд өнгө, фонт, хэмжээний стиль өгнө: <code>&lt;p style="color:red;"&gt;</code></li>
                    <li><code>lang</code> — хуудасны хэлийг тодорхойлно: <code>&lt;html lang="mn"&gt;</code></li>
                    <li><code>title</code> — хулгана дээгүүр нь гарахад харагдах зөвлөмж текст</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<p title=\"Нэмэлт мэдээлэл\" style=\"color:red;\">Улаан текст, дээр нь хулгана байрлуулж үзээрэй.</p>\n<a href=\"https://site.mn\" target=\"_blank\">Шинэ таб дээр нээх холбоос</a>"),
            ),
            $chapter(
                'HTML Гарчигууд',
                'headings',
                'h1-h6 түвшний гарчиг, SEO-ийн ач холбогдол.',
                <<<'HTMLCONTENT'
                <p>Гарчиг нь веб хуудасны бүтцийг тодорхойлоход маш чухал ач холбогдолтой.</p>
                <h3>Гарчигийн түвшнүүд</h3>
                <p>HTML-д <code>&lt;h1&gt;</code>-ээс <code>&lt;h6&gt;</code> хүртэлх 6 түвшний гарчиг байдаг — тоо ихсэх тусам гарчиг жижигрэн, ач холбогдол буурна.</p>
                <h3>Яагаад гарчиг чухал вэ?</h3>
                <ul>
                    <li><strong>Хайлтын систем (SEO):</strong> Google зэрэг хайлтын системүүд таны веб хуудасны агуулга, бүтцийг гарчгуудаар нь дамжуулан индексжүүлж ойлгодог.</li>
                    <li><strong>Хэрэглэгчийн туршлага:</strong> Уншигчид гарчгийг харан хуудасны гол сэдвийг шууд баримжаалдаг.</li>
                    <li><code>&lt;h1&gt;</code> гарчгийг хуудас бүрт заавал нэг удаа, үндсэн сэдэвт хэрэглэх нь тохиромжтой. Текстийг зүгээр л том эсвэл тод харагдуулахын тулд гарчиг tag-ийг бүү ашиглаарай!</li>
                </ul>
                <h3>Хэвтээ шугам ба шинэ мөр</h3>
                <p><code>&lt;hr&gt;</code> tag нь сэдэв хооронд заагч хэвтээ зураас татдаг, <code>&lt;br&gt;</code> tag нь шинэ параграф эхлүүлэхгүйгээр шууд шинэ мөрөнд шилжүүлдэг.</p>
                HTMLCONTENT,
                $htmlStarter("<h1>1-р зэргийн гарчиг (Хамгийн том)</h1>\n<h2>2-р зэргийн гарчиг</h2>\n<h3>3-р зэргийн гарчиг</h3>\n<hr>\n<h6>6-р зэргийн гарчиг (Хамгийн жижиг)</h6>"),
            ),
            $chapter(
                'HTML Параграфууд',
                'paragraphs',
                'Эх бичвэрийг цогцолборт хуваах, <pre> тегийн ялгаа.',
                <<<'HTMLCONTENT'
                <p>Эх бичвэрийг хэсэгчлэн хувааж бичихэд параграф элементүүдийг ашигладаг.</p>
                <h3>Параграф үүсгэх: &lt;p&gt;</h3>
                <p><code>&lt;p&gt;Энэ бол эхний параграф.&lt;/p&gt;</code> — браузер параграф бүрийн өмнө болон ард автоматаар бага зэргийн зай (margin) авдаг.</p>
                <h3>Браузерын текст цэгцлэх ойлголт</h3>
                <p>Та HTML кодондоо хичнээн олон хоосон зай эсвэл шинэ мөр авсан ч, браузер түүнийг ердөө ГАНЦ хоосон зай гэж уншдаг. Олон мөрөнд бичигдсэн текст ч браузер дээр нэг мөр болж харагдана.</p>
                <h3>Хэлбэр хадгалах tag: &lt;pre&gt;</h3>
                <p>Хэрэв та бичсэн шинэ мөр, хоосон зайг яг хэвээр нь харагдуулахыг хүсвэл <code>&lt;pre&gt;</code> (preformatted text) tag-ийг ашиглана — жишээ нь шүлэг, кодын хэсэг харуулахад тохиромжтой.</p>
                HTMLCONTENT,
                $htmlStarter("<p>Энэ бол эхний параграф.</p>\n<p>Энэ бол хоёр дахь параграф.</p>\n\n<pre>\n  Шүлэг эсвэл кодын хэсэг:\n  Эхний мөр\n    Зай авсан мөр\n  Төгсгөлийн мөр\n</pre>"),
            ),
            $chapter(
                'HTML Хэв маяг',
                'styles',
                'style аттрибутаар өнгө, фонт, зэрэгцүүлэлт тохируулах.',
                <<<'HTMLCONTENT'
                <p>HTML элементүүдийн өнгө, дэвсгэр, фонт, хэмжээ зэргийг <code>style</code> аттрибутын тусламжтайгаар өөрчилж болно.</p>
                <h3>Синтакс бүтэц</h3>
                <p><code>&lt;tagname style="property:value;"&gt;</code></p>
                <h3>Түгээмэл хэрэглэгддэг хэв маягийн шинж чанарууд</h3>
                <ul>
                    <li><strong>Дэвсгэр өнгө:</strong> <code>style="background-color: lightblue;"</code></li>
                    <li><strong>Бичвэрийн өнгө:</strong> <code>style="color: red;"</code></li>
                    <li><strong>Фонтын төрөл, хэмжээ:</strong> <code>style="font-family: Arial; font-size: 16px;"</code></li>
                    <li><strong>Текст зэрэгцүүлэлт:</strong> <code>style="text-align: center;"</code></li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<body style=\"background-color: lightblue;\">\n<h1 style=\"background-color: yellow; text-align: center;\">Шар дэвсгэртэй гарчиг</h1>\n<p style=\"color: red; font-family: Arial;\">Улаан өнгөтэй текст.</p>\n</body>"),
            ),
            $chapter(
                'HTML Текст форматжуулалт',
                'formatting',
                'b, strong, i, em, mark, small, del, ins, sub, sup тегүүд.',
                <<<'HTMLCONTENT'
                <p>Текстийг тусгай агуулгаар онцлох, хэлбэржүүлэхэд зориулсан HTML tag-ууд:</p>
                <ul>
                    <li><code>&lt;b&gt;</code> — энгийн тод текст (Bold)</li>
                    <li><code>&lt;strong&gt;</code> — чухал ач холбогдолтой тод текст</li>
                    <li><code>&lt;i&gt;</code> — налуу текст (Italic)</li>
                    <li><code>&lt;em&gt;</code> — ач холбогдол заасан налуу текст (Emphasized)</li>
                    <li><code>&lt;mark&gt;</code> — шар өнгөөр тодруулсан текст (Highlighted)</li>
                    <li><code>&lt;small&gt;</code> — жижигрүүлсэн текст</li>
                    <li><code>&lt;del&gt;</code> — дундуур нь зураас татсан текст (Deleted)</li>
                    <li><code>&lt;ins&gt;</code> — доогуур нь зураастай шинэ текст (Inserted)</li>
                    <li><code>&lt;sub&gt;</code> — доод индекс, жишээ нь химийн H<sub>2</sub>O</li>
                    <li><code>&lt;sup&gt;</code> — дээд индекс, жишээ нь математикийн X<sup>2</sup></li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<p><b>Тод текст</b> ба <strong>чухал текст</strong></p>\n<p><i>Налуу текст</i> ба <mark>маркердсан текст</mark></p>\n<p><del>Хуучин үнэ 100,000₮</del> <ins>Шинэ үнэ 80,000₮</ins></p>\n<p>H<sub>2</sub>O ба X<sup>2</sup></p>"),
            ),
            $chapter(
                'HTML Тайлбар / Коммент',
                'comments',
                'Браузер дээр харагддаггүй тайлбар мөр бичих.',
                <<<'HTMLCONTENT'
                <p>Код бичиж байхдаа өөртөө болон хамтран ажиллагсаддаа зориулж тайлбар үлдээж болно. Тайлбар нь браузер дээр харагддаггүй.</p>
                <h3>Синтакс</h3>
                <p><code>&lt;!-- Энэ бол HTML тайлбар хэсэг юм. Браузер үүнийг харуулахгүй. --&gt;</code></p>
                <h3>Ашиглах зориулалт</h3>
                <ul>
                    <li>Кодын бүтцийг ойлгомжтой болгох, хэсгүүдийг зааглах.</li>
                    <li>Алдаа шалгах (debugging) үед зарим кодыг түр идэвхигүй болгох.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<!-- Энэ бол HTML тайлбар хэсэг юм. Браузер үүнийг харуулахгүй. -->\n<p>Энэ бол харагдах текст.</p>\n<!-- <p>Туршилтаар түр хаасан код</p> -->"),
            ),
            $chapter(
                'HTML Өнгөнүүд',
                'colors',
                'Өнгөний нэр, HEX, RGB утгаар тодорхойлох.',
                <<<'HTMLCONTENT'
                <p>HTML дээр өнгөнүүдийг өнгөний нэр, RGB, эсвэл HEX утгаар тодорхойлно.</p>
                <h3>Өнгөний нэрээр</h3>
                <p>Стандарт 140 гаруй өнгөний нэр байдаг. Жишээ: <code>Red</code>, <code>Blue</code>, <code>Green</code>, <code>Tomato</code>, <code>Orange</code>, <code>DodgerBlue</code>, <code>Gray</code>.</p>
                <h3>HEX утгаар</h3>
                <p>16-тын тооллын системээр <code>#RRGGBB</code> хэлбэрээр бичигддэг, утга 00-оос FF хүртэл байна: <code>#ff0000</code> (улаан), <code>#00ff00</code> (ногоон), <code>#0000ff</code> (цэнхэр), <code>#000000</code> (хар), <code>#ffffff</code> (цагаан).</p>
                <h3>RGB утгаар</h3>
                <p><code>rgb(red, green, blue)</code> форматтай бөгөөд өнгө тус бүр 0-ээс 255 хүртэл тоо авна. <code>rgba(255, 99, 71, 0.5)</code> шиг дөрөв дэх утга (alpha) нэмвэл өнгийг хагас тунгалаг болгоно.</p>
                HTMLCONTENT,
                $htmlStarter("<p style=\"color: rgb(255, 99, 71);\">RGB өнгөтэй текст</p>\n<p style=\"background-color: #ff0000; color: white;\">HEX улаан дэвсгэртэй текст</p>\n<p style=\"background-color: rgba(255, 99, 71, 0.5);\">50% тунгалаг RGBA өнгө</p>"),
            ),
            $chapter(
                'HTML ба CSS',
                'css',
                'CSS-ийг HTML-тэй холбох гурван арга.',
                <<<'HTMLCONTENT'
                <p><strong>CSS</strong> (Cascading Style Sheets) нь веб хуудасны гадаад үзэмжийг тохируулдаг.</p>
                <h3>1. Inline CSS (Шууд tag дотор)</h3>
                <p>Элементийн <code>style</code> аттрибутад шууд бичнэ: <code>&lt;h1 style="color:blue;"&gt;Шууд стиль авсан гарчиг&lt;/h1&gt;</code></p>
                <h3>2. Internal CSS (Дотоод стиль)</h3>
                <p>Хуудасны <code>&lt;head&gt;</code> хэсэгт <code>&lt;style&gt;</code> tag дотор бичнэ: <code>&lt;style&gt;body { background-color: #f0f0f0; }&lt;/style&gt;</code></p>
                <h3>3. External CSS (Гадаад файл холбох)</h3>
                <p>Хамгийн шилдэг арга — тусдаа <code>style.css</code> файл үүсгээд <code>&lt;link&gt;</code> tag-аар холбоно: <code>&lt;link rel="stylesheet" href="style.css"&gt;</code></p>
                HTMLCONTENT,
                $cssStarter,
            ),
            $chapter(
                'HTML Холбоос / Линкүүд',
                'links',
                'a tag, href, target, mailto/tel линкүүд.',
                <<<'HTMLCONTENT'
                <p>Веб хуудаснаас өөр хуудас эсвэл өөр веб сайт руу шилжих холбоосыг <code>&lt;a&gt;</code> tag-аар хийнэ.</p>
                <h3>Үндсэн синтакс</h3>
                <p><code>&lt;a href="https://www.example.com"&gt;Энд дарж шилжинэ үү&lt;/a&gt;</code></p>
                <h3>Target аттрибут</h3>
                <ul>
                    <li><code>target="_self"</code> (үндсэн тохиргоо) — одоогийн таб дээр нээнэ.</li>
                    <li><code>target="_blank"</code> — шинэ цонх эсвэл шинэ таб дээр нээнэ.</li>
                </ul>
                <h3>И-мэйл болон утасны линк</h3>
                <p><code>&lt;a href="mailto:info@example.com"&gt;И-мэйл илгээх&lt;/a&gt;</code> болон <code>&lt;a href="tel:+97699112233"&gt;Утасдах&lt;/a&gt;</code></p>
                HTMLCONTENT,
                $htmlStarter("<a href=\"https://www.example.com\" target=\"_blank\">Энд дарж шилжинэ үү</a><br>\n<a href=\"mailto:info@example.com\">И-мэйл илгээх</a><br>\n<a href=\"tel:+97699112233\">Утасдах</a>"),
            ),
            $chapter(
                'HTML Зургууд',
                'images',
                'img tag, src, alt, width, height аттрибутууд.',
                <<<'HTMLCONTENT'
                <p>Веб хуудаст зураг байршуулахад <code>&lt;img&gt;</code> хоосон tag-ийг ашиглана.</p>
                <h3>Синтакс болон чухал аттрибутууд</h3>
                <p><code>&lt;img src="images/nature.jpg" alt="Байгалийн үзэмж" width="500" height="300"&gt;</code></p>
                <ul>
                    <li><code>src</code> — зургийн замаас файлын байршлыг заана.</li>
                    <li><code>alt</code> — зураг уншигдаагүй үед эсвэл харааны бэрхшээлтэй хүмүүсийн дэлгэц уншигчид (screen reader) уншиж өгөх текст.</li>
                    <li><code>width</code>, <code>height</code> — зургийн хэмжээг пикселээр заана.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<img src=\"https://via.placeholder.com/500x300\" alt=\"Байгалийн үзэмж\" width=\"500\" height=\"300\">"),
            ),
            $chapter(
                'HTML Хуудасны гарчиг',
                'page-title',
                'title элементийн ач холбогдол.',
                <<<'HTMLCONTENT'
                <p><code>&lt;title&gt;</code> элемент нь веб хуудасны гарчгийг тодорхойлдог бөгөөд хуудасны <code>&lt;head&gt;</code> хэсэгт бичигдэнэ.</p>
                <h3>Ач холбогдол</h3>
                <ul>
                    <li>Браузерын таб дээр харагдах нэр болно.</li>
                    <li>Хэрэглэгч хуудсыг Bookmark (хавчуурга)-д хадгалахад энэ нэрээр хадгалагдана.</li>
                    <li>Google зэрэг хайлтын системд илэрц болж харагдах үндсэн гарчиг болно.</li>
                </ul>
                HTMLCONTENT,
                <<<'HTMLSTARTER'
                <!DOCTYPE html>
                <html>
                <head>
                <title>HTML Сурах Анхан Шатны Заавар</title>
                </head>
                <body>

                <h1>Веб хуудасны гарчиг жишээ</h1>
                <p>Энэ хуудасны браузер таб дээрх нэрийг харна уу.</p>

                </body>
                </html>
                HTMLSTARTER,
            ),
            $chapter(
                'HTML Хүснэгтүүд',
                'tables',
                'table, tr, th, td, colspan, rowspan.',
                <<<'HTMLCONTENT'
                <p>Өгөгдлийг мөр болон багананд эрэмбэлж харуулахад <code>&lt;table&gt;</code> элемент хэрэглэгдэнэ.</p>
                <h3>Хүснэгтийн бүтэц элементүүд</h3>
                <ul>
                    <li><code>&lt;table&gt;</code> — хүснэгтийг эхлүүлж төгсгөнө.</li>
                    <li><code>&lt;tr&gt;</code> (Table Row) — хүснэгтийн нэг мөр.</li>
                    <li><code>&lt;th&gt;</code> (Table Header) — хүснэгтийн толгой нүд (тод, төвдөө зэрэгцсэн байдаг).</li>
                    <li><code>&lt;td&gt;</code> (Table Data) — хүснэгтийн энгийн өгөгдлийн нүд.</li>
                </ul>
                <h3>Нүд нэгтгэх (colspan &amp; rowspan)</h3>
                <ul>
                    <li><code>colspan="2"</code> — хэвтээ чиглэлд хоёр баганыг нэгтгэнэ.</li>
                    <li><code>rowspan="2"</code> — босоо чиглэлд хоёр мөрийг нэгтгэнэ.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<table border=\"1\">\n  <tr>\n    <th>Дугаар</th>\n    <th>Барааны нэр</th>\n    <th>Үнэ</th>\n  </tr>\n  <tr>\n    <td>1</td>\n    <td>Компьютер</td>\n    <td>2,500,000₮</td>\n  </tr>\n  <tr>\n    <td>2</td>\n    <td>Хулгана</td>\n    <td>50,000₮</td>\n  </tr>\n</table>"),
            ),
            $chapter(
                'HTML Жагсаалтууд',
                'lists',
                'Дугааргүй, дугаартай, тодорхойлолтын жагсаалт.',
                <<<'HTMLCONTENT'
                <p>Мэдээллийг жагсаалт хэлбэрээр цэгцтэй харуулах боломж.</p>
                <h3>1. Дугааргүй жагсаалт (Unordered List) — &lt;ul&gt;</h3>
                <p>Дараалал чухал биш үед ашиглана, дотор нь <code>&lt;li&gt;</code> тус бүр нэг зүйл.</p>
                <h3>2. Дугаартай жагсаалт (Ordered List) — &lt;ol&gt;</h3>
                <p>Дараалал чухал үед ашиглана, браузер автоматаар дугаарлана.</p>
                <h3>3. Тодорхойлолтын жагсаалт (Description List) — &lt;dl&gt;</h3>
                <p><code>&lt;dt&gt;</code> нэр томьёо, <code>&lt;dd&gt;</code> түүний тайлбарыг холбож харуулна.</p>
                HTMLCONTENT,
                $htmlStarter("<ul>\n  <li>Алим</li>\n  <li>Банан</li>\n  <li>Лемон</li>\n</ul>\n\n<ol>\n  <li>Эхний алхам</li>\n  <li>Дараагийн алхам</li>\n  <li>Сүүлчийн алхам</li>\n</ol>\n\n<dl>\n  <dt>HTML</dt>\n  <dd>- Веб хуудасны бүтэц тодорхойлох хэл</dd>\n  <dt>CSS</dt>\n  <dd>- Веб хуудасны гадаад дизайныг тохируулах хэл</dd>\n</dl>"),
            ),
            $chapter(
                'HTML Блок ба Инлайн',
                'block-inline',
                'Block-level ба Inline элементүүдийн ялгаа.',
                <<<'HTMLCONTENT'
                <p>HTML-ийн бүх элементүүдийг браузер дээрх зан төлөвөөр нь 2 үндсэн ангилалд хуваадаг.</p>
                <h3>1. Блок элементүүд (Block-level Elements)</h3>
                <ul>
                    <li>Үргэлж шинэ мөрнөөс эхэлдэг.</li>
                    <li>Эзлэх боломжтой бүх өргөнийг (100%) хамран эзэлдэг.</li>
                    <li>Жишээ: <code>&lt;div&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>-...-<code>&lt;h6&gt;</code>, <code>&lt;ul&gt;</code>, <code>&lt;form&gt;</code>, <code>&lt;header&gt;</code>, <code>&lt;footer&gt;</code>.</li>
                </ul>
                <h3>2. Инлайн элементүүд (Inline Elements)</h3>
                <ul>
                    <li>Шинэ мөрнөөс эхлэхгүй, текст дотор шууд үргэлжилдэг.</li>
                    <li>Зөвхөн агуулгадаа шаардлагатай өргөнийг л эзэлнэ.</li>
                    <li>Жишээ: <code>&lt;span&gt;</code>, <code>&lt;a&gt;</code>, <code>&lt;b&gt;</code>, <code>&lt;i&gt;</code>, <code>&lt;img&gt;</code>, <code>&lt;button&gt;</code>.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<div style=\"background:#e2e8f0; padding:10px;\">Энэ бол block элемент — бүтэн мөрийг эзэлнэ.</div>\n<p>Энд <span style=\"color:blue\">inline элемент</span> текстийн дунд урсаж байна.</p>"),
            ),
            $chapter(
                'HTML Div элемент',
                'div',
                'Бүлэглэх, агуулах контейнер блок элемент.',
                <<<'HTMLCONTENT'
                <p><code>&lt;div&gt;</code> элемент нь бусад HTML элементүүдийг бүлэглэх, агуулдаг контейнер (сав) блок элемент юм.</p>
                <h3>Ашиглах шалтгаан</h3>
                <p><code>&lt;div&gt;</code> элемент нь өөрөө ямар нэгэн онцгой дизайнгүй боловч CSS-тэй хамтарснаар веб хуудасны эх бэлтгэл, багана, хэсгүүдийг хуваан бүтэцжүүлэх хамгийн чухал элемент болдог.</p>
                HTMLCONTENT,
                $htmlStarter("<div style=\"background-color: #e2e8f0; padding: 15px;\">\n  <h2>Бүлэг хэсгийн гарчиг</h2>\n  <p>Энэ div доторх параграф бичвэр.</p>\n</div>"),
            ),
            $chapter(
                'HTML Класс',
                'classes',
                'class аттрибутаар олон элементийг нэгэн зэрэг загварчлах.',
                <<<'HTMLCONTENT'
                <p><code>class</code> аттрибут нь HTML элементүүдэд ижил ангилал, нэр олгож, CSS болон JavaScript дээр дуудаж ажиллахад хэрэглэгдэнэ.</p>
                <h3>Онцлог</h3>
                <ul>
                    <li>Нэг хуудас дээр ижил классын нэрийг олон элементэд давтан ашиглаж болно.</li>
                    <li>CSS дээр класс нэрийг дуудахдаа өмнө нь цэг (<code>.</code>) тавьдаг: <code>.card { ... }</code>.</li>
                </ul>
                HTMLCONTENT,
                <<<'HTMLSTARTER'
                <!DOCTYPE html>
                <html>
                <head>
                <style>
                .card {
                    background: #f8fafc;
                    border: 1px solid #cbd5e1;
                    padding: 10px;
                    margin-bottom: 5px;
                }
                </style>
                </head>
                <body>

                <div class="card">1-р карт</div>
                <div class="card">2-р карт</div>

                </body>
                </html>
                HTMLSTARTER,
            ),
            $chapter(
                'HTML Дахин давтагдашгүй ID',
                'id',
                'id аттрибут, class-тай харьцуулсан ялгаа.',
                <<<'HTMLCONTENT'
                <p><code>id</code> аттрибут нь тухайн веб хуудас дээрх ердөө ГАНЦХАН давтагдашгүй элементэд тусгай нэр олгодог.</p>
                <h3>Class ба ID-ын ялгаа</h3>
                <ul>
                    <li><strong>Давтагдах боломж:</strong> Class олон элементэд ашиглаж болно, ID хуудаст заавал ганц байна.</li>
                    <li><strong>CSS тэмдэглэгээ:</strong> Class цэгээр эхэлнэ (<code>.classname</code>), ID паундаар эхэлнэ (<code>#idname</code>).</li>
                </ul>
                HTMLCONTENT,
                <<<'HTMLSTARTER'
                <!DOCTYPE html>
                <html>
                <head>
                <style>
                #main-header {
                    color: darkblue;
                    text-align: center;
                }
                </style>
                </head>
                <body>

                <h1 id="main-header">Үндсэн Том Гарчиг</h1>

                </body>
                </html>
                HTMLSTARTER,
            ),
            $chapter(
                'HTML Товчлуурууд',
                'buttons',
                'button tag ба type=button/submit/reset.',
                <<<'HTMLCONTENT'
                <p>Дардаг товчлуур үүсгэхэд <code>&lt;button&gt;</code> tag-ийг ашиглана.</p>
                <h3>Төрлүүд (type)</h3>
                <ul>
                    <li><code>type="button"</code> — энгийн товчлуур (JavaScript-тэй хамт хэрэглэгддэг).</li>
                    <li><code>type="submit"</code> — формын өгөгдлийг сервер рүү илгээх товчлуур.</li>
                    <li><code>type="reset"</code> — формын бичсэн мэдээллийг цэвэрлэх товчлуур.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<button type=\"button\" onclick=\"alert('Сайн байна уу!')\">Намайг дараарай</button>"),
            ),
            $chapter(
                'HTML Файлын зам',
                'file-paths',
                'Абсолют ба релатив файлын зам.',
                <<<'HTMLCONTENT'
                <p>Веб хуудаст зураг, CSS, JS файл холбоход файлын замыг зөв тохируулах шаардлагатай.</p>
                <h3>1. Абсолют зам (Absolute Path)</h3>
                <p>Интернэт дэх файлын бүрэн веб хаяг: <code>&lt;img src="https://www.example.com/images/logo.png"&gt;</code></p>
                <h3>2. Релатив / Харьцангуй зам (Relative Path)</h3>
                <ul>
                    <li><code>src="picture.jpg"</code> — одоогийн файлыг агуулж буй хавтас дотор байрлана.</li>
                    <li><code>src="images/picture.jpg"</code> — одоогийн хавтас доторх <code>images</code> нэртэй дэд хавтас дотор байрлана.</li>
                    <li><code>src="../picture.jpg"</code> — одоогийн хавтаснаас нэг түвшин дээш хавтаст байрлана.</li>
                </ul>
                HTMLCONTENT,
                $htmlStarter("<p>Дараах зам харьцангуй (relative) зам юм:</p>\n<code>&lt;img src=\"images/picture.jpg\"&gt;</code>"),
            ),
            $chapter(
                'HTML Head толгой хэсэг',
                'head',
                'title, meta, link, style, script элементүүд.',
                <<<'HTMLCONTENT'
                <p><code>&lt;head&gt;</code> элемент нь браузер болон хайлтын системд зориулсан металл мэдээллийн контейнер юм.</p>
                <h3>&lt;head&gt; дотор бичигдэх элементүүд</h3>
                <ul>
                    <li><code>&lt;title&gt;</code> — хуудасны гарчиг.</li>
                    <li><code>&lt;meta&gt;</code> — кодчилол, түлхүүр үг, тайлбар, гар утасны дэлгэцийн харагдац (viewport).</li>
                    <li><code>&lt;link&gt;</code> — гадаад CSS файл холбох.</li>
                    <li><code>&lt;style&gt;</code> — дотоод CSS стиль бичих.</li>
                    <li><code>&lt;script&gt;</code> — JavaScript файл эсвэл код холбох.</li>
                </ul>
                HTMLCONTENT,
                <<<'HTMLSTARTER'
                <!DOCTYPE html>
                <html>
                <head>
                <meta charset="UTF-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Веб Хуудас</title>
                </head>
                <body>

                <h1>Head хэсэгтэй хуудас</h1>
                <p>Энэ хуудасны head хэсэгт meta болон title орсон.</p>

                </body>
                </html>
                HTMLSTARTER,
            ),
            $chapter(
                'HTML Маягт / Формууд',
                'forms',
                'form, input, textarea, select элементүүд.',
                <<<'HTMLCONTENT'
                <p>Хэрэглэгчээс мэдээлэл авч сервер рүү илгээхэд HTML формыг ашигладаг.</p>
                <h3>Формын үндсэн бүтэц: &lt;form&gt;</h3>
                <p><code>&lt;form action="/submit-data" method="POST"&gt; ... &lt;/form&gt;</code></p>
                <h3>Input төрлүүд</h3>
                <ul>
                    <li><code>text</code> — энгийн нэг мөр текст оруулах.</li>
                    <li><code>password</code> — нууц үг оруулах (тэмдэгтүүд нууцлагдана).</li>
                    <li><code>email</code> — и-мэйл хаяг оруулах шалгалттай.</li>
                    <li><code>radio</code> — олон сонголтоос зөвхөн нэгийг сонгох.</li>
                    <li><code>checkbox</code> — олон сонголтоос нэг эсвэл олныг сонгох.</li>
                    <li><code>submit</code> — илгээх товчлуур.</li>
                </ul>
                <h3>Бусад формын элементүүд</h3>
                <p><code>&lt;textarea&gt;</code> — олон мөр текст оруулах. <code>&lt;select&gt;</code> ба <code>&lt;option&gt;</code> — унадаг цэсээс сонголт хийх.</p>
                HTMLCONTENT,
                $htmlStarter("<form action=\"/process.php\" method=\"POST\">\n  <div>\n    <label>Хэрэглэгчийн нэр:</label><br>\n    <input type=\"text\" name=\"username\" placeholder=\"Нэрээ оруулна уу\" required>\n  </div><br>\n  <div>\n    <label>Нууц үг:</label><br>\n    <input type=\"password\" name=\"password\" required>\n  </div><br>\n  <div>\n    <label>Хот сонгох:</label><br>\n    <select name=\"city\">\n      <option value=\"ub\">Улаанбаатар</option>\n      <option value=\"darkhan\">Дархан</option>\n    </select>\n  </div><br>\n  <div>\n    <input type=\"submit\" value=\"Нэвтрэх\">\n  </div>\n</form>"),
            ),
        ];
    }

    /**
     * The 25-chapter CSS curriculum, adapted from "CSS Анхан Шатны Бүрэн
     * Гарын Авлага" — one category per chapter, one lesson per category.
     *
     * @return array<int, array{name: string, slug: string, description: string, lessons: array<int, array{title: string, slug: string, content: string, starter: ?string}>}>
     */
    private function cssBookCategories(\Closure $cssPageStarter): array
    {
        $chapter = fn (string $title, string $slug, string $description, string $content, ?string $starter) => [
            'name' => $title,
            'slug' => $slug,
            'description' => $description,
            'lessons' => [[
                'title' => $title,
                'slug' => $slug,
                'content' => $content,
                'starter' => $starter,
            ]],
        ];

        return [
            $chapter(
                'CSS Танилцуулга',
                'introduction',
                'CSS гэж юу вэ, HTML-тэй ямар харилцаатай вэ.',
                <<<'HTMLCONTENT'
                <p><strong>CSS</strong> гэдэг нь <em>Cascading Style Sheets</em> (Дараалсан хэв маягийн хуудас) гэсэн үгийн хураангуй юм. HTML нь веб хуудасны араг яс болон агуулгыг (текст, зураг, товчлуур) бүтээдэг бол, CSS нь тэрхүү агуулгад хувцас өмсүүлж, гоёж чимэглэдэг — өнгө тохируулах, үсгийн хэмжээ өөрчлөх, байршил зааж өгөх — хэл юм.</p>
                <h3>Яагаад CSS ашигладаг вэ?</h3>
                <ul>
                    <li><strong>Харагдах байдлыг тохируулах:</strong> веб хуудсан дээрх элементүүдийн өнгө, хэмжээ, фонт, зай, байршлыг бүрэн удирдана.</li>
                    <li><strong>Цаг хугацаа, ажил хөнгөвчлөх:</strong> нэг CSS файлаар хэдэн зуун HTML хуудасны загварыг нэг дор өөрчлөх боломжтой.</li>
                    <li><strong>Төхөөрөмжид зохицох (Responsive Design):</strong> гар утас, планшет, компьютер зэрэг өөр өөр дэлгэцэнд веб сайтыг гоё харагдуулахад тусална.</li>
                </ul>
                <p><strong>Санамж:</strong> HTML бол барилгын тоосго, багана; CSS бол будаг, чимэглэл, өрөөний дотоод дизайн юм.</p>
                HTMLCONTENT,
                $cssPageStarter("h1 {\n    color: #2563eb;\n    text-align: center;\n}", "<h1>Сайн байна уу, CSS!</h1>\n<p>Энэ бол CSS-ээр загварчлагдсан анхны хуудас.</p>"),
            ),
            $chapter(
                'CSS Синтаксис',
                'syntax',
                'Сонгогч, шинж чанар, утга, буржгар хаалт, цэгтэй таслал.',
                <<<'HTMLCONTENT'
                <p>CSS код бичих тусгай дүрэм буюу синтаксис байдаг. CSS дүрэм нь <strong>сонгогч (selector)</strong> болон <strong>тунхаглалын блок (declaration block)</strong>-оос бүрдэнэ: <code>h1 { color: blue; font-size: 12px; }</code></p>
                <h3>Синтаксисын бүрэлдэхүүн хэсгүүд</h3>
                <ul>
                    <li><strong>Сонгогч (Selector):</strong> хэлбэржүүлэх гэж буй HTML элементийг заана (жишээнд <code>h1</code> tag).</li>
                    <li><strong>Шинж чанар (Property):</strong> өөрчлөхийг хүсэж буй хэв маягийн нэр — жишээ нь <code>color</code> (өнгө), <code>font-size</code> (үсгийн хэмжээ).</li>
                    <li><strong>Утга (Value):</strong> шинж чанарт олгож буй тодорхой утга — жишээ нь <code>blue</code>, <code>12px</code>.</li>
                    <li><strong>Буржгар хаалт <code>{ }</code>:</strong> шинж чанаруудын жагсаалтыг эхлүүлж, төгсгөж хаана.</li>
                    <li><strong>Цэгтэй таслал <code>;</code>:</strong> шинж чанар ба утгын хослол бүрийн төгсгөлд заавал бичигдэж, дараагийн шинжийг заглана.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("h1 {\n    color: blue;\n    font-size: 28px;\n}", '<h1>Синтаксисын жишээ гарчиг</h1>'),
            ),
            $chapter(
                'CSS Сонгогч',
                'selectors',
                'Таг, ID, Class, Universal, Бүлэглэсэн сонгогчид.',
                <<<'HTMLCONTENT'
                <p>CSS сонгогч нь HTML хуудсан дээрх ямар элементийг онцолж, стилийг нь өөрчлөхийг хайж олоход хэрэглэгдэнэ. Анхан шатанд дараах 5 үндсэн сонгогчийг эзэмших шаардлагатай:</p>
                <ul>
                    <li><strong>Таг сонгогч (Element):</strong> <code>p { color: red; }</code> — хуудас дээрх бүх <code>&lt;p&gt;</code> tag-ийг сонгоно.</li>
                    <li><strong>ID сонгогч:</strong> <code>#header { background: black; }</code> — <code>id="header"</code> аттрибуттай цорын ганц элементийг сонгоно, <code>#</code> тэмдгээр эхэлнэ.</li>
                    <li><strong>Class сонгогч:</strong> <code>.button { color: white; }</code> — <code>class="button"</code> аттрибуттай бүх элементүүдийг сонгоно, <code>.</code> тэмдгээр эхэлнэ.</li>
                    <li><strong>Бүхнийг сонгогч (Universal):</strong> <code>* { margin: 0; }</code> — веб хуудас дээрх бүх HTML элементийг нэгэн зэрэг сонгоно.</li>
                    <li><strong>Бүлэглэсэн сонгогч (Grouping):</strong> <code>h1, h2, p { text-align: center; }</code> — олон элементийг таслалаар тусгаарлан нэг ижил стильтэй болгоно.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("p { color: red; }\n#header { background: #1d4ed8; color: white; padding: 10px; }\n.button { color: darkgreen; font-weight: bold; }\nh1, h2 { text-align: center; }", "<h1 id=\"header\">ID сонгогчоор загварчлагдсан</h1>\n<h2>Бүлэглэсэн сонгогч</h2>\n<p>Таг сонгогчоор улаан болсон текст.</p>\n<p class=\"button\">Class сонгогчоор ногоон болсон текст.</p>"),
            ),
            $chapter(
                'CSS Холбох аргууд',
                'how-to',
                'External, Internal, Inline CSS.',
                <<<'HTMLCONTENT'
                <p>CSS кодыг HTML баримттай холбох 3 үндсэн арга байдаг.</p>
                <h3>1. Гадаад CSS (External CSS) — хамгийн шилдэг арга</h3>
                <p>CSS кодоо тусдаа <code>style.css</code> файлд бичиж, HTML хуудасныхаа <code>&lt;head&gt;</code> хэсэгт холбоно: <code>&lt;link rel="stylesheet" href="style.css"&gt;</code></p>
                <h3>2. Дотоод CSS (Internal CSS)</h3>
                <p>HTML файлынхаа <code>&lt;head&gt;</code> хэсэгт <code>&lt;style&gt;</code> tag нээж кодоо бичнэ.</p>
                <h3>3. Шууд CSS (Inline CSS)</h3>
                <p>HTML элементийн <code>style</code> аттрибут дотор шууд бичнэ: <code>&lt;h1 style="color:blue; text-align:center;"&gt;Энэ бол гарчиг&lt;/h1&gt;</code>. Зөвхөн туршилтын журмаар ашиглахад тохиромжтой.</p>
                HTMLCONTENT,
                $cssPageStarter("body { background-color: linen; }\nh1 { color: maroon; }", "<h1>Дотоод CSS-ээр загварчлагдсан гарчиг</h1>\n<h1 style=\"color:blue; text-align:center;\">Inline style-тэй гарчиг</h1>"),
            ),
            $chapter(
                'CSS Тайлбар',
                'comments',
                '/* */ хэлбэрийн тайлбар мөр.',
                <<<'HTMLCONTENT'
                <p>Тайлбар (comment) нь кодонд тайлбар хийх, санамж бичих эсвэл зарим кодыг түр хугацаанд идэвхгүй болгоход хэрэглэгддэг. Тайлбар нь браузер дээр харагдахгүй ба зөвхөн код бичиж буй хүнд зориулагдана.</p>
                <p>CSS тайлбар нь <code>/*</code> тэмдгээр эхэлж, <code>*/</code> тэмдгээр төгсөнө:</p>
                <p><code>/* Энэ бол нэг мөрт тайлбар юм */</code></p>
                <p>Олон мөртэй тайлбар бичихдээ ч мөн адил <code>/* ... */</code> хооронд хүссэн хэмжээгээрээ бичиж болно.</p>
                HTMLCONTENT,
                $cssPageStarter("/* Гарчгийн өнгийг тохируулах */\nh1 {\n    color: #2563eb;\n}\n\np {\n    color: red; /* Текстийн өнгийг улаан болгоно */\n}", "<h1>Тайлбартай CSS жишээ</h1>\n<p>Энэ параграф улаан өнгөтэй.</p>"),
            ),
            $chapter(
                'CSS Өнгө',
                'colors',
                'Нэрээр, HEX, RGB, RGBA утгаар өнгө тодорхойлох.',
                <<<'HTMLCONTENT'
                <p>CSS-д өнгийг хэд хэдэн өөр форматаар тодорхойлж болно.</p>
                <ul>
                    <li><strong>Өнгөний нэрээр:</strong> <code>red</code>, <code>blue</code>, <code>green</code>, <code>tomato</code>, <code>dodgerblue</code> г.м (140 гаруй стандарт нэр бий).</li>
                    <li><strong>HEX код:</strong> <code>#FF0000</code> (улаан), <code>#00FF00</code> (ногоон), <code>#0000FF</code> (цэнхэр) — 6 оронтой 16-тын тоолол.</li>
                    <li><strong>RGB утга:</strong> <code>rgb(red, green, blue)</code> — 0-ээс 255 хүртэлх тоо, жишээ нь <code>rgb(255, 99, 71)</code>.</li>
                    <li><strong>RGBA (тунгалагтай өнгө):</strong> <code>rgba(255, 99, 71, 0.5)</code> — сүүлчийн <code>0.5</code> нь 50% тунгалаг байдлыг заана (0 = тунгалаг, 1 = тодорхой).</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("h1 {\n    color: #2563eb; /* Текстийн өнгө */\n    background-color: rgba(37, 99, 235, 0.1); /* Дэвсгэр өнгө */\n}\np { color: tomato; }", "<h1>HEX болон RGBA өнгө</h1>\n<p>RGB нэрээр өгсөн улбар шар өнгөтэй параграф.</p>"),
            ),
            $chapter(
                'CSS Фон / Ар тал',
                'backgrounds',
                'background-color, image, repeat, position, attachment.',
                <<<'HTMLCONTENT'
                <p>Элементийн ар талын дэвсгэрийг хэлбэржүүлэх шинж чанарууд:</p>
                <ul>
                    <li><code>background-color</code> — ар талын өнгийг тохируулна.</li>
                    <li><code>background-image</code> — ар талд зураг оруулна: <code>url("bg.jpg")</code>.</li>
                    <li><code>background-repeat</code> — зураг давтагдахыг удирдана (<code>repeat</code>, <code>no-repeat</code>, <code>repeat-x</code>, <code>repeat-y</code>).</li>
                    <li><code>background-position</code> — зургийн байршлыг заана (<code>center</code>, <code>top right</code>).</li>
                    <li><code>background-attachment</code> — скролдоход зураг хамт хөдлөх үү, бэхлэгдэх үү (<code>scroll</code>, <code>fixed</code>).</li>
                </ul>
                <p>Хураангуй бичиглэл (shorthand): <code>background: #ffffff url("img.jpg") no-repeat right top;</code></p>
                HTMLCONTENT,
                $cssPageStarter("body {\n    background-color: #f1f5f9;\n}\ndiv.banner {\n    background-color: #2563eb;\n    color: white;\n    padding: 20px;\n    text-align: center;\n}", "<div class=\"banner\">Ар тал өнгөтэй баннер</div>\n<p>Хуудасны фон өнгө light slate.</p>"),
            ),
            $chapter(
                'CSS Хүрээ',
                'borders',
                'border-style, width, color, radius.',
                <<<'HTMLCONTENT'
                <p><code>border</code> шинж чанар нь элементийн эргэн тойронд хүрээ зурахад хэрэглэгдэнэ.</p>
                <h3>Үндсэн шинжүүд</h3>
                <ul>
                    <li><code>border-style</code> — хүрээний зураасан хэлбэр: <code>solid</code> (үргэлжилсэн), <code>dashed</code> (тасархай), <code>dotted</code> (цэглэсэн), <code>double</code> (давхар).</li>
                    <li><code>border-width</code> — хүрээний зузаан, жишээ нь <code>2px</code>.</li>
                    <li><code>border-color</code> — хүрээний өнгө.</li>
                    <li><code>border-radius</code> — хүрээний өнцгийг дугуйруулах, жишээ нь <code>border-radius: 8px;</code>.</li>
                </ul>
                <p>Хураангуй бичиглэл (Зузаан | Хэлбэр | Өнгө): <code>border: 2px solid #2563eb;</code></p>
                HTMLCONTENT,
                $cssPageStarter("div {\n    border: 2px solid #2563eb;\n    border-radius: 10px;\n    padding: 15px;\n    margin-bottom: 10px;\n}\ndiv.dashed {\n    border: 3px dashed tomato;\n}", "<div>Дугуй өнцөгтэй хатуу хүрээ</div>\n<div class=\"dashed\">Тасархай хүрээ</div>"),
            ),
            $chapter(
                'CSS Гадна зааг',
                'margins',
                'margin, хураангуй бичиглэл, margin: 0 auto.',
                <<<'HTMLCONTENT'
                <p>Margin (гадна зай) нь элементийн хүрээний ГАДНА талд хоосон зай үүсгэн, бусад элементүүдээс түлхэж заглахад хэрэглэгдэнэ.</p>
                <h3>Хураангуй бичиглэлийн дүрмүүд</h3>
                <ul>
                    <li><code>margin: 10px 20px 30px 40px;</code> — Дээд | Баруун | Доод | Зүүн (цагийн зүүний дагуу)</li>
                    <li><code>margin: 10px 20px;</code> — [Дээд+Доод: 10px] [Баруун+Зүүн: 20px]</li>
                    <li><code>margin: 0 auto;</code> — элементийг эцэг контейнерийн голд байрлуулахад ашигладаг.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("div.box {\n    width: 200px;\n    background-color: #e2e8f0;\n    padding: 10px;\n}\ndiv.centered {\n    width: 200px;\n    margin: 20px auto;\n    background-color: #bfdbfe;\n    padding: 10px;\n}", "<div class=\"box\">Ердийн margin-тай хайрцаг</div>\n<div class=\"centered\">margin: auto ашиглан голлуулсан хайрцаг</div>"),
            ),
            $chapter(
                'CSS Дотор зааг',
                'padding',
                'padding, хүрээ ба агуулгын хоорондох зай.',
                <<<'HTMLCONTENT'
                <p>Padding (дотор зай) нь элементийн хүрээ ба агуулга (текст/зураг) хоорондох ДОТОР талын хоосон зайг үүсгэнэ.</p>
                <p><strong>Анхаарах зүйл:</strong> padding нэмэхэд элементийн бодит харагдах өргөн ба өндөр томордог! (CSS Box Model хичээлээс тодорхой харна уу).</p>
                HTMLCONTENT,
                $cssPageStarter("div {\n    padding: 20px; /* Бүх дөрвөн талдаа 20px дотор зай авна */\n    background-color: #f1f5f9;\n    border: 1px solid #cbd5e1;\n}", "<div>Дотор зайтай хайрцаг — текст хүрээнээс зайтай харагдана.</div>"),
            ),
            $chapter(
                'CSS Өндөр ба Өргөн',
                'height-width',
                'px, %, max-width хэмжигдэхүүнүүд.',
                <<<'HTMLCONTENT'
                <p>Элементийн өндөр ба өргөнийг тохируулахад <code>height</code> болон <code>width</code> шинж чанаруудыг ашиглана.</p>
                <ul>
                    <li><strong>Пиксел (px):</strong> тогтмол хэмжээ заана, жишээ нь <code>width: 300px;</code>.</li>
                    <li><strong>Хувь (%):</strong> эцэг элементийнхээ хэдэн хувийг эзлэхийг заана, жишээ нь <code>width: 100%;</code>.</li>
                    <li><code>max-width</code> — элементийн эзлэх хамгийн их өргөн. Дэлгэцийн хэмжээ жижгэрэхэд автоматаар агшиж, скролл баар гарахаас сэргийлнэ.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("div.card {\n    max-width: 400px;\n    width: 100%;\n    height: auto;\n    background-color: #dbeafe;\n    padding: 15px;\n}", "<div class=\"card\">Энэ хайрцаг 400px-ээс ихгүй өргөнтэй, жижиг дэлгэцэнд 100% сунана.</div>"),
            ),
            $chapter(
                'CSS Хайрцаган загвар',
                'box-model',
                'Content, Padding, Border, Margin, box-sizing.',
                <<<'HTMLCONTENT'
                <p>HTML дээрх бүх элементүүдийг CSS-д "дөрвөлжин хайрцаг" (box) гэж үздэг. Box Model нь 4 дараалсан үеэс бүрдэнэ:</p>
                <ol>
                    <li><strong>Content (Агуулга):</strong> текст, зураг эсвэл видео байрлах төв хэсэг.</li>
                    <li><strong>Padding (Дотор зай):</strong> агуулгын эргэн тойрон дахь дотор зай.</li>
                    <li><strong>Border (Хүрээ):</strong> padding болон content-ийг хүрээлсэн зураас.</li>
                    <li><strong>Margin (Гадна зай):</strong> хүрээний гадна талын, бусад элементээс тусгаарлах зай.</li>
                </ol>
                <p><strong>Алтан дүрэм (box-sizing):</strong> элементэд <code>width: 100px; padding: 10px; border: 5px solid;</code> гэж өгвөл бодит өргөн нь 100 + 20 + 10 = 130px болно! Иймээс тооцооллыг хялбарчлахын тулд ямагт <code>box-sizing: border-box;</code> тохиргоог ашигладаг.</p>
                HTMLCONTENT,
                $cssPageStarter("* {\n    box-sizing: border-box; /* Padding ба Border-ийг өргөн дотор нь багтаана */\n}\ndiv {\n    width: 200px;\n    padding: 20px;\n    border: 5px solid #2563eb;\n    background-color: #dbeafe;\n}", "<div>box-sizing: border-box ашигласан тул энэ хайрцаг яг 200px өргөнтэй хэвээр байна.</div>"),
            ),
            $chapter(
                'CSS Гадна хүрээ',
                'outline',
                'outline, border-тэй харьцуулсан ялгаа.',
                <<<'HTMLCONTENT'
                <p>Outline (гадна хүрээ) нь элементийн <code>border</code>-ийн ГАДУУР зурагдах нэмэлт зураас юм.</p>
                <h3>Border ба Outline-ийн ялгаа</h3>
                <ul>
                    <li>Outline нь эх бэлтгэлийн хэмжээнд (layout) нөлөөлөхгүй, зай эзэлдэггүй.</li>
                    <li>Outline-ийг ихэвчлэн форм дээр фокуслах (button эсвэл input идэвхжихэд) үед онцлоход ашигладаг.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("p {\n    border: 1px solid black;\n    outline: 3px solid red;\n    outline-offset: 5px; /* Border ба Outline хоорондох зай */\n    padding: 10px;\n}", "<p>Хар хүрээтэй, улаан outline-тай параграф.</p>"),
            ),
            $chapter(
                'CSS Текст хэлбэржүүлэлт',
                'text',
                'text-align, decoration, transform, letter-spacing, line-height, shadow.',
                <<<'HTMLCONTENT'
                <p>Текстийн харагдах байдлыг өөрчлөх үндсэн шинж чанарууд:</p>
                <ul>
                    <li><code>text-align</code> — текст зэрэгцүүлэлт (<code>left</code>, <code>center</code>, <code>right</code>, <code>justify</code>).</li>
                    <li><code>text-decoration</code> — доогуур/дээгүүр зураас (<code>none</code>, <code>underline</code>, <code>line-through</code>). Холбоосын доогуур зураасыг арилгахад <code>text-decoration: none;</code> ашиглана.</li>
                    <li><code>text-transform</code> — үсгийн жижиг/том хэлбэр (<code>uppercase</code>, <code>lowercase</code>, <code>capitalize</code>).</li>
                    <li><code>letter-spacing</code> — үсэг хоорондын зай, жишээ нь <code>2px</code>.</li>
                    <li><code>line-height</code> — мөр хоорондын зай, жишээ нь <code>1.6</code>.</li>
                    <li><code>text-shadow</code> — текстэнд сүүдэр оруулах: <code>text-shadow: 2px 2px 4px #000;</code>.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("h1 {\n    text-align: center;\n    text-transform: uppercase;\n    letter-spacing: 2px;\n    text-shadow: 2px 2px 4px #94a3b8;\n}\np {\n    line-height: 1.8;\n    text-decoration: underline;\n}", "<h1>Хэлбэржүүлсэн гарчиг</h1>\n<p>Энэ параграф өргөн мөр хоорондын зайтай, доогуур зурастай.</p>"),
            ),
            $chapter(
                'CSS Фонт / Үсгийн хэв маяг',
                'fonts',
                'font-family, font-size, font-weight, font-style.',
                <<<'HTMLCONTENT'
                <p>Фонтын сонголт нь веб сайтын уншигдах чанарт шууд нөлөөлнө.</p>
                <h3>Үндсэн шинж чанарууд</h3>
                <ul>
                    <li><code>font-family</code> — фонтын нэрс, жишээ нь <code>"Segoe UI", Arial, sans-serif</code>. Хэрэв эхний фонт байхгүй бол дараагийнхыг сонгоно (fallback).</li>
                    <li><code>font-size</code> — үсгийн хэмжээ (<code>16px</code>, <code>1rem</code>, <code>1.2em</code>).</li>
                    <li><code>font-weight</code> — үсгийн зузаан (<code>normal</code>, <code>bold</code>, 100-аас 900 хүртэлх тоо).</li>
                    <li><code>font-style</code> — хэв маяг (<code>normal</code>, <code>italic</code>).</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("body {\n    font-family: 'Helvetica Neue', Arial, sans-serif;\n    font-size: 16px;\n}\nh1 {\n    font-weight: bold;\n}\nem {\n    font-style: italic;\n}", "<h1>Тод фонттой гарчиг</h1>\n<p>Энгийн параграф ба <em>налуу</em> текст.</p>"),
            ),
            $chapter(
                'CSS Дүрс тэмдэг',
                'icons',
                'Font Awesome зэрэг icon сан холбох.',
                <<<'HTMLCONTENT'
                <p>Веб хуудсанд дүрс тэмдэг (icon) оруулах хамгийн хялбар арга бол Font Awesome эсвэл Google Material Icons зэрэг бэлэн сангуудыг холбох явдал юм.</p>
                <p>Эдгээр дүрс тэмдгүүд нь фонт шиг ажилладаг тул CSS-ийн <code>color</code> болон <code>font-size</code> ашиглан өнгө, хэмжээг нь өөрчилж болно.</p>
                <p>Ашиглахын тулд эхлээд icon сангийн CSS-ийг <code>&lt;head&gt;</code> дотор <code>&lt;link&gt;</code>-ээр холбож, дараа нь <code>&lt;i class="fa-solid fa-envelope"&gt;&lt;/i&gt;</code> шиг tag ашиглана.</p>
                HTMLCONTENT,
                $cssPageStarter(".my-icon {\n    color: #2563eb;\n    font-size: 32px;\n}", "<link rel=\"stylesheet\" href=\"https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css\">\n<i class=\"fa-solid fa-envelope my-icon\"></i>\n<i class=\"fa-solid fa-heart my-icon\" style=\"color:crimson;\"></i>"),
            ),
            $chapter(
                'CSS Холбоос',
                'links',
                ':link, :visited, :hover, :active псевдо-класс.',
                <<<'HTMLCONTENT'
                <p>HTML-ийн <code>&lt;a&gt;</code> холбоос tag нь хэрэглэгчийн үйлдэл дээр үндэслэн 4 төрлийн псевдо-класс (pseudo-class) төлөвтэй байдаг:</p>
                <ul>
                    <li><code>a:link</code> — анхны, зочилж үзээгүй холбоос.</li>
                    <li><code>a:visited</code> — хэрэглэгчийн урьд нь зочилсон холбоос.</li>
                    <li><code>a:hover</code> — хулгана холбоос дээгүүр ирэх үеийн төлөв.</li>
                    <li><code>a:active</code> — хулганы даралт хийгдэж буй агшин.</li>
                </ul>
                <p>Эдгээрийг ашиглан холбоосыг товчлуур (button) хэлбэртэй болгож болно.</p>
                HTMLCONTENT,
                $cssPageStarter("a.btn {\n    background-color: #2563eb;\n    color: white;\n    padding: 10px 20px;\n    text-decoration: none;\n    display: inline-block;\n    border-radius: 5px;\n}\na.btn:hover {\n    background-color: #1d4ed8; /* Хулгана очиход бараан цэнхэр болно */\n}", "<a class=\"btn\" href=\"#\">Товчлуур хэлбэртэй холбоос</a>"),
            ),
            $chapter(
                'CSS Жагсаалт',
                'lists',
                'list-style-type, position, image.',
                <<<'HTMLCONTENT'
                <p>HTML-ийн <code>&lt;ul&gt;</code> (эрэмбэгүй) болон <code>&lt;ol&gt;</code> (эрэмбэтэй) жагсаалтуудыг хэлбэржүүлэх шинжүүд:</p>
                <ul>
                    <li><code>list-style-type</code> — маркерын хэлбэрийг өөрчилнө (<code>disc</code>, <code>circle</code>, <code>square</code>, <code>decimal</code>, <code>none</code>).</li>
                    <li><code>list-style-position</code> — маркер жагсаалтын дотор эсвэл гадна байхыг заана (<code>inside</code>, <code>outside</code>).</li>
                    <li><code>list-style-image</code> — цэг эсвэл дугаарын оронд зураг ашиглана.</li>
                </ul>
                <p>Веб сайтын цэс (navigation menu) хийхэд ихэвчлэн <code>list-style-type: none;</code> ашиглан цэгийг арилгадаг.</p>
                HTMLCONTENT,
                $cssPageStarter("ul.nav {\n    list-style-type: none;\n    margin: 0;\n    padding: 0;\n}\nul.nav li {\n    display: inline-block;\n    margin-right: 15px;\n}\nul.square {\n    list-style-type: square;\n}", "<ul class=\"nav\">\n    <li>Нүүр</li>\n    <li>Тухай</li>\n    <li>Холбоо барих</li>\n</ul>\n<ul class=\"square\">\n    <li>Дөрвөлжин маркертай</li>\n    <li>Жагсаалтын зүйл</li>\n</ul>"),
            ),
            $chapter(
                'CSS Хүснэгт',
                'tables',
                'border-collapse, зебра хэв маяг.',
                <<<'HTMLCONTENT'
                <p>HTML хүснэгтийг (<code>&lt;table&gt;</code>) цэвэрхэн, уншихад хялбар болгон хэлбэржүүлэх дүрмүүд:</p>
                <ul>
                    <li><code>border-collapse: collapse;</code> — хүснэгтийн хүрээг нийлүүлж нэг зураас болгоно.</li>
                    <li><code>th, td { padding: 12px; }</code> — нүд бүрт дотор зай өгч уншихад хялбар болгоно.</li>
                    <li><code>tr:nth-child(even)</code> — сүлжилж өнгө өөрчлөх "зебра" хэв маяг үүсгэнэ.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("table {\n    width: 100%;\n    border-collapse: collapse;\n}\nth, td {\n    padding: 12px;\n    border-bottom: 1px solid #ddd;\n    text-align: left;\n}\ntr:nth-child(even) {\n    background-color: #f2f2f2;\n}", "<table>\n    <tr><th>Нэр</th><th>Оноо</th></tr>\n    <tr><td>Бат</td><td>95</td></tr>\n    <tr><td>Сараа</td><td>88</td></tr>\n    <tr><td>Дорж</td><td>76</td></tr>\n</table>"),
            ),
            $chapter(
                'CSS Дэлгэцийн горим',
                'display',
                'block, inline, inline-block, none.',
                <<<'HTMLCONTENT'
                <p><code>display</code> шинж чанар нь элемент дэлгэцэнд хэрхэн харагдах, хэрхэн байрлахыг шийддэг хамгийн чухал шинжүүдийн нэг юм.</p>
                <ul>
                    <li><strong>block:</strong> дэлгэцийн бүх өргөнийг эзэлж, шинэ мөрнөөс эхэлнэ (жишээ нь <code>&lt;div&gt;</code>, <code>&lt;p&gt;</code>, <code>&lt;h1&gt;</code>). Өндөр, өргөн тохируулж болно.</li>
                    <li><strong>inline:</strong> зөвхөн агуулгын өргөнийг эзэлж, нэг мөрөнд цуварч байрлана (жишээ нь <code>&lt;span&gt;</code>, <code>&lt;a&gt;</code>). Өндөр, өргөн тохируулах боломжгүй!</li>
                    <li><strong>inline-block:</strong> мөрөнд цуварч харагдах боловч <code>width</code> болон <code>height</code> өндөр өргөнийг авдаг давуу талтай.</li>
                    <li><strong>none:</strong> элементийг дэлгэцээс бүрэн нууна (бүтэц болон зай нь устсан мэт харагдана).</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter(".block-demo { display: block; background: #dbeafe; margin-bottom: 5px; }\n.inline-demo { display: inline; background: #fef08a; }\n.inline-block-demo { display: inline-block; width: 100px; background: #bbf7d0; padding: 5px; }", "<span class=\"block-demo\">block элемент</span>\n<span class=\"inline-demo\">inline 1</span><span class=\"inline-demo\">inline 2</span>\n<span class=\"inline-block-demo\">inline-block</span><span class=\"inline-block-demo\">inline-block 2</span>"),
            ),
            $chapter(
                'CSS Байршил',
                'position',
                'static, relative, absolute, fixed, sticky.',
                <<<'HTMLCONTENT'
                <p><code>position</code> шинж чанар нь элементийн веб хуудас дээрх байршлыг нарийн тодорхойлно.</p>
                <ul>
                    <li><code>static</code> — анхдагч төлөв, хуудасны ердийн урсгалаар байрлана (<code>top</code>, <code>left</code> ажиллахгүй).</li>
                    <li><code>relative</code> — анхны байсан байрлалаасаа харьцангуй шилжинэ.</li>
                    <li><code>absolute</code> — хамгийн ойрын <code>position: relative</code> бүхий эцэг элементтэйгээ харьцуулан байрлана.</li>
                    <li><code>fixed</code> — дэлгэцийн цонхонд (viewport) бэхлэгдэнэ, скролдоход байрлал нь өөрчлөгдөхгүй.</li>
                    <li><code>sticky</code> — скролдож тодорхой зайд хүрэхэд <code>fixed</code> мэт бэхлэгдэнэ.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter(".parent { position: relative; width: 300px; height: 150px; background: #e2e8f0; }\n.child { position: absolute; top: 10px; right: 10px; background: #2563eb; color: white; padding: 5px 10px; }", "<div class=\"parent\">\n    Эцэг элемент (relative)\n    <div class=\"child\">Хүү (absolute)</div>\n</div>"),
            ),
            $chapter(
                'CSS Агуулгын хальж гаралт',
                'overflow',
                'visible, hidden, scroll, auto.',
                <<<'HTMLCONTENT'
                <p>Элементийн агуулга (текст/зураг) нь өөрийн зааж өгсөн өндөр эсвэл өргөнөөс хэтэрч халих үед <code>overflow</code> шинж чанараар тохируулна.</p>
                <ul>
                    <li><code>overflow: visible;</code> — анхдагч утга, хальсан агуулга хүрээний гадуур шууд ил харагдана.</li>
                    <li><code>overflow: hidden;</code> — хальж гарсан илүүдэл агуулгыг таслан нууна.</li>
                    <li><code>overflow: scroll;</code> — хальсан эсэхээс үл хамааран үргэлж скролл баар гаргана.</li>
                    <li><code>overflow: auto;</code> — зөвхөн агуулга хальсан үед л автоматаар скролл баар гаргана (хамгийн хэрэглээтэй нь).</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter(".box {\n    width: 200px;\n    height: 80px;\n    border: 1px solid #94a3b8;\n    overflow: auto;\n}", "<div class=\"box\">\n    Энэ хайрцгийн өндөр 80px тул удаан текст автоматаар доторх скролл баараар гарна. Энд илүү их текст нэмж, скролл ажиллаж байгааг шалгаж үзээрэй.\n</div>"),
            ),
            $chapter(
                'CSS Хөвөгч байрлал',
                'float',
                'float: left/right, clear: both.',
                <<<'HTMLCONTENT'
                <p><code>float</code> шинж чанарыг анх зургийн хажуугаар текст урсаж харагдах дизайныг хийхэд зориулан бүтээжээ.</p>
                <ul>
                    <li><code>float: left;</code> — элементийг зүүн тал руу шахаж хөвүүлнэ.</li>
                    <li><code>float: right;</code> — элементийг баруун тал руу шахаж хөвүүлнэ.</li>
                    <li><code>clear: both;</code> — хөвөгч элементүүдийн нөлөөг арилгаж, дараагийн мөрнөөс эхлүүлнэ.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter(".float-box {\n    float: left;\n    width: 80px;\n    height: 80px;\n    background: #2563eb;\n    margin-right: 15px;\n}\n.clear {\n    clear: both;\n}", "<div class=\"float-box\"></div>\n<p>Энэ текст хөвөгч дөрвөлжингийн баруун талд урсаж харагдана.</p>\n<div class=\"clear\"></div>\n<p>Энэ хэсэг clear:both-ийн ачаар шинэ мөрнөөс эхэлнэ.</p>"),
            ),
            $chapter(
                'CSS Зэрэгцүүлэлт',
                'align',
                'text-align, margin: auto, padding голлуулах.',
                <<<'HTMLCONTENT'
                <p>Элементийг хэвтээ болон босоо чиглэлд голуулах, зэрэгцүүлэх техникүүд:</p>
                <h3>1. Текст ба inline элементийг голуулах</h3>
                <p><code>div { text-align: center; }</code></p>
                <h3>2. Дөрвөлжин блок (block) элементийг хэвтээд голуулах</h3>
                <p>Заавал <code>width</code> (өргөн) тохируулсан байх ба margins-ийг <code>auto</code> болгоно: <code>.center-block { width: 60%; margin: 0 auto; }</code></p>
                <h3>3. Босоо чиглэлд голуулах (padding ашиглах)</h3>
                <p><code>.vertical-center { padding: 50px 0; }</code> — дээд доод талдаа тэнцүү зай авна.</p>
                HTMLCONTENT,
                $cssPageStarter(".text-centered { text-align: center; }\n.center-block { width: 60%; margin: 0 auto; background: #dbeafe; padding: 10px; }", "<div class=\"text-centered\">Голлуулсан текст</div>\n<div class=\"center-block\">margin: auto ашигласан голлуулсан блок</div>"),
            ),
            $chapter(
                'CSS Нийлмэл сонгогч',
                'combinators',
                'Descendant, Child, Adjacent Sibling, General Sibling.',
                <<<'HTMLCONTENT'
                <p>Нийлмэл сонгогч нь олон сонгогчдын хоорондын иерархи (удамшлын ба эцэг-хүүгийн) хамаарлыг тодорхойлно.</p>
                <ul>
                    <li><strong><code>div p</code> (Descendant/Удам):</strong> <code>&lt;div&gt;</code> дотор байрлах БҮХ <code>&lt;p&gt;</code> tag-ийг сонгоно (хэдэн үеийн дотор байсан ч хамаагүй).</li>
                    <li><strong><code>div &gt; p</code> (Child/Шууд хүү):</strong> зөвхөн <code>&lt;div&gt;</code>-ийн шууд доод үеийн (хүү) <code>&lt;p&gt;</code> tag-ийг сонгоно.</li>
                    <li><strong><code>div + p</code> (Adjacent Sibling/Чанх ард):</strong> <code>&lt;div&gt;</code>-ийн гадна талд чанх араас нь дагалдах ганц <code>&lt;p&gt;</code> элементийг сонгоно.</li>
                    <li><strong><code>div ~ p</code> (General Sibling/Ерөнхий дагавар):</strong> <code>&lt;div&gt;</code>-ийн дараа байрлах бүх ах дүү <code>&lt;p&gt;</code> элементүүдийг сонгоно.</li>
                </ul>
                HTMLCONTENT,
                $cssPageStarter("div p {\n    color: blue; /* div дотрох бүх p (удам) */\n}\ndiv > p {\n    font-weight: bold; /* div-ийн шууд хүү p */\n}\ndiv + p {\n    color: red; /* div-ийн чанх ард ирэх p */\n}", "<div>\n    <p>Div дотор шууд байрлах p (bold + blue)</p>\n    <div><p>Div дотор давхарлагдсан p (зөвхөн blue)</p></div>\n</div>\n<p>Div-ийн чанх ард ирэх p (улаан)</p>\n<p>Дараагийн ах дүү p</p>"),
            ),
        ];
    }

    /**
     * @return array<int, array{name: string, slug: string, description: string, lessons: array<int, array{title: string, slug: string, content: string, starter: string}>}>
     */
    private function cppBookCategories(): array
    {
        $lesson = fn (string $title, string $slug, string $content, string $starter) => [
            'title' => $title, 'slug' => $slug, 'content' => $content, 'starter' => $starter,
        ];

        $category = fn (string $name, string $slug, string $description, array $lessons) => [
            'name' => $name, 'slug' => $slug, 'description' => $description, 'lessons' => $lessons,
        ];

        return [
            $category('Эхлэл, Синтакс ба Гаралт', 'intro-syntax-output', 'C++ гэж юу вэ, орчноо бэлдэх, эхний програмаа бичье.', [
                $lesson('C++ хэлний танилцуулга', 'cpp-intro', <<<'HTMLCONTENT'
                <p><strong>C++</strong> бол дэлхийн хамгийн хурдан бөгөөд өргөн хэрэглээтэй програмчлалын хэлнүүдийн нэг. 1979 онд C хэл дээр суурилж, объект хандлагат (OOP) боломжуудыг нэмж хөгжүүлсэн.</p>
                <h3>C++ хаана ашиглагддаг вэ?</h3>
                <ul>
                    <li>Үйлдлийн систем (Windows, Linux)</li>
                    <li>Тоглоомын хөдөлгүүр (Game Engines) — жишээ нь Unity, Unreal</li>
                    <li>Санхүүгийн систем, өндөр хурдны тооцоолол</li>
                    <li>Хиймэл интеллект, робот техникийн программ хангамжийн суурь</li>
                </ul>
                <p>Хурд, санах ойн шууд удирдлага нь C++-ийг гүйцэтгэлийн шаардлага өндөртэй програм бичихэд тохиромжтой болгодог.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"C++ heleer ehelj bailgaa!\" << endl;\n    return 0;\n}"),

                $lesson('Ажиллаж эхлэх ба орчин бэлтгэх', 'cpp-get-started', <<<'HTMLCONTENT'
                <p>C++ дээр код бичиж ажиллуулахын тулд хоёр үндсэн зүйл хэрэгтэй.</p>
                <h3>Хэрэгцээт програмууд</h3>
                <ul>
                    <li><strong>Текст редактор:</strong> Кодоо бичих орчин — Dev-c++, Visual Studio Code, Sublime text</li>
                    <li><strong>Компилятор (Compiler):</strong> Бичсэн кодыг машины код руу хөрвүүлэгч — жишээ нь GCC/G++, Clang.</li>
                </ul>
                <p>Code::Dev-c++, Visual Studio зэрэг IDE (Integrated Development Environment) нь редактор болон компиляторыг хамтад нь агуулдаг тул анхлан суралцагчид тохиромжтой сонголт юм.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Compiler zöv tohirgootoi esehiig shalgah\n    cout << \"Orchin belen!\" << endl;\n    return 0;\n}"),

                $lesson('Програмын бүтцийн синтакс', 'cpp-syntax', <<<'HTMLCONTENT'
                <p>Дурын C++ програм тодорхой давтагдах бүтэцтэй байдаг. Энэ бүтцийг мэдэх нь код унших, бичихэд чухал.</p>
                <h3>Бүтцийн задаргаа</h3>
                <ul>
                    <li><code>#include &lt;iostream&gt;</code> — оролт/гаралтын сангийн файлыг холбоно</li>
                    <li><code>using namespace std;</code> — стандарт сангийн нэрсийн орон зайг (std) ашиглахыг зөвшөөрнө</li>
                    <li><code>int main() { ... }</code> — програм эхлэн ажиллах үндсэн функц; код бүр эндээс эхэлнэ</li>
                    <li><code>return 0;</code> — програм амжилттай дуусснаа үйлдлийн системд мэдээлнэ</li>
                </ul>
                <p>Мөр бүрийн төгсгөлд цэгтэй таслал (<code>;</code>) заавал тавина.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"Ene bol C++ programyn zurag torol\" << endl;\n    return 0;\n}"),

                $lesson('Дэлгэцэд хэвлэх гаралт', 'cpp-output', <<<'HTMLCONTENT'
                <p>Дэлгэц дээр текст эсвэл утга хэвлэн гаргахдаа <code>cout</code> объект болон <code>&lt;&lt;</code> операторыг ашигладаг.</p>
                <h3>Шинэ мөрөнд шилжих</h3>
                <ul>
                    <li><code>endl</code> — мөр солиход зориулсан манипулятор</li>
                    <li><code>\n</code> — тэмдэгт мөр дотор шууд ашиглаж болох newline тэмдэгт</li>
                </ul>
                <p>Нэг <code>cout</code> мэдэгдэлд хэд хэдэн утгыг <code>&lt;&lt;</code> тэмдэгтээр залгаж хэвлэж болно.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"Negdeh mur\" << endl;\n    cout << \"Hoyordoh mur\" << \"\\n\" << \"Daraagiin mur\";\n    return 0;\n}"),
            ]),

            $category('Тайлбар, Хувьсагч ба Өгөгдлийн төрлүүд', 'comments-variables-types', 'Кодоо тайлбарлах, өгөгдлөө хувьсагчид хадгалах, төрлүүдийг таних.', [
                $lesson('Тайлбар бичих', 'cpp-comments', <<<'HTMLCONTENT'
                <p>Тайлбар (comment) нь компилятор хөрвүүлэхгүй орхидог, зөвхөн кодыг ойлгомжтой болгоход зориулагдсан бичвэр юм.</p>
                <h3>Тайлбарын хоёр төрөл</h3>
                <ul>
                    <li><strong>Нэг мөрийн тайлбар:</strong> <code>//</code> тэмдэгтээс мөрийн төгсгөл хүртэл</li>
                    <li><strong>Олон мөрийн тайлбар:</strong> <code>/*</code>-ээс эхэлж <code>*/</code>-ээр төгсдөг блок</li>
                </ul>
                <p>Тайлбарыг ихэвчлэн кодын зорилго, тайлбар шаардсан хэсгүүдийг тодруулахад ашигладаг.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    // Ene bol neg muriin tailbar\n    /* Ene bol\n       olon muriin tailbar */\n    cout << \"Tailbar hereglev\" << endl;\n    return 0;\n}"),

                $lesson('Хувьсагч үүсгэх ба утга олгох', 'cpp-variables', <<<'HTMLCONTENT'
                <p>Хувьсагч (variable) гэдэг нь санах ойд тодорхой өгөгдлийг хадгалах хаяглагдсан "хайрцаг" юм.</p>
                <h3>Хувьсагч зарлах синтакс</h3>
                <p><code>ТӨРӨЛ НЭР = УТГА;</code></p>
                <ul>
                    <li>Хувьсагчийн нэр заавал үсэг эсвэл доогуур зураас (<code>_</code>)-аар эхэлнэ</li>
                    <li>Тоо эсвэл тусгай тэмдэгтээр эхэлж болохгүй</li>
                    <li>C++ нь регистр мэдэрдэг (case-sensitive): <code>age</code> ба <code>Age</code> өөр хувьсагч</li>
                </ul>
                <p>Доорх жишээнд бүхэл болон бодит тоон хувьсагчид зарлаж, дэлгэцэд хэвлэж байна.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int age = 20;\n    float price = 19.99;\n    cout << \"Age: \" << age << \", Price: \" << price << endl;\n    return 0;\n}"),

                $lesson('Хэрэглэгчийн оролт авах', 'cpp-user-input', <<<'HTMLCONTENT'
                <p>Гараас хэрэглэгчийн оруулсан утгыг авахдаа <code>cin</code> объект болон <code>&gt;&gt;</code> операторыг ашиглана.</p>
                <h3>cin ашиглах дараалал</h3>
                <ul>
                    <li>Эхлээд хэрэглэгчид зориулсан мессежийг <code>cout</code>-оор хэвлэнэ (Энэ үйлдэл нь заавал хийгдэх шаардлагагүй)</li>
                    <li>Дараа нь <code>cin &gt;&gt; хувьсагч;</code> гэж бичиж, гараас оруулсан утгыг хувьсагчид хадгална</li>
                </ul>
                <p>Симуляц консол горимд оролт бодитоор ажиллахгүй тул жишээг унших замаар ойлгоорой.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int x;\n    cout << \"Too oruulna uu: \";\n    cin >> x;\n    cout << \"Ta oruulsan too: \" << x;\n    return 0;\n}"),

                $lesson('Өгөгдлийн төрлүүд', 'cpp-data-types', <<<'HTMLCONTENT'
                <p>C++ хэл нь өгөгдлийг нарийн төрөлжүүлдэг, санах ойгоо шууд удирддаг хэл юм.</p>
                <h3>Үндсэн өгөгдлийн төрлүүд</h3>
                <ul>
                    <li><code>int</code> — бүхэл тоо (жишээ нь <code>int count = 100;</code>)</li>
                    <li><code>float</code> / <code>double</code> — бутархай тоо, <code>double</code> илүү нарийвчлалтай</li>
                    <li><code>char</code> — нэг тэмдэгт, ганц хашилтанд бичигдэнэ (жишээ нь <code>'Z'</code>)</li>
                    <li><code>string</code> — тэмдэгтүүдийн цуваа текст, олон хашилтанд бичигдэнэ</li>
                    <li><code>bool</code> — <code>true</code> эсвэл <code>false</code> логик утга</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int count = 100;\n    double pi = 3.14159;\n    char letter = 'Z';\n    bool active = true;\n    cout << count << \" \" << pi << \" \" << letter << \" \" << active;\n    return 0;\n}"),
            ]),

            $category('Оператор, Тэмдэгт мөр ба Математик', 'operators-strings-math', 'Хувьсагч дээр үйлдэл хийх, текст боловсруулах, тооцоо хийх.', [
                $lesson('Операторууд', 'cpp-operators', <<<'HTMLCONTENT'
                <p>Оператор нь хувьсагч болон утгууд дээр үйлдэл хийхэд хэрэглэгддэг тэмдэгт юм.</p>
                <h3>Арифметик оператор</h3>
                <ul>
                    <li><code>+ - * /</code> — нэмэх, хасах, үржүүлэх, хуваах</li>
                    <li><code>%</code> — хуваалтын үлдэгдэл (жишээ нь <code>10 % 3 = 1</code>)</li>
                    <li><code>++</code> / <code>--</code> — 1-ээр нэмэгдүүлэх/хасах</li>
                </ul>
                <h3>Харьцуулах ба логик оператор</h3>
                <ul>
                    <li><code>== != &gt; &lt; &gt;= &lt;=</code> — харьцуулах</li>
                    <li><code>&amp;&amp;</code> (БА), <code>||</code> (ЭСВЭЛ), <code>!</code> (ҮГҮЙСГЭЛ)</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int a = 10, b = 3;\n    cout << \"Nemeh: \" << a + b << endl;\n    cout << \"Uldegdel: \" << a % b << endl;\n    return 0;\n}"),

                $lesson('Тэмдэгт мөр', 'cpp-strings', <<<'HTMLCONTENT'
                <p>Текстэн өгөгдөлтэй ажиллахын тулд <code>&lt;string&gt;</code> сан ашиглана. Тэмдэгт мөрүүдийг холбох, урт олох зэрэг үйлдлийг хялбар хийж болно.</p>
                <h3>Түгээмэл үйлдлүүд</h3>
                <ul>
                    <li><code>+</code> оператор — хоёр мөрийг залгах</li>
                    <li><code>.length()</code> — мөрийн урт олох</li>
                    <li><code>.append(...)</code> — мөрийн ард нэмэлт текст залгах</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\n#include <string>\nusing namespace std;\n\nint main() {\n    string firstName = \"Bat\";\n    string lastName = \"Dorj\";\n    string fullName = firstName + \" \" + lastName;\n    cout << fullName << endl;\n    cout << fullName.length() << endl;\n    return 0;\n}"),

                $lesson('Математик функцүүд', 'cpp-math', <<<'HTMLCONTENT'
                <p>Математикийн ахисан шатны тооцоолол хийхэд <code>&lt;cmath&gt;</code> сангийн бэлэн функцүүдийг ашиглаж болно.</p>
                <h3>Түгээмэл ашиглагддаг функцүүд</h3>
                <ul>
                    <li><code>max(a, b)</code> / <code>min(a, b)</code> — их/бага утгыг олох</li>
                    <li><code>sqrt(x)</code> — квадрат язгуур</li>
                    <li><code>pow(x, y)</code> — зэрэг дэвшүүлэх (x-ийг y зэрэгт)</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\n#include <cmath>\nusing namespace std;\n\nint main() {\n    cout << \"Ih utga: \" << max(5, 10) << endl;\n    cout << \"Kvadrat yazguur: \" << sqrt(16) << endl;\n    return 0;\n}"),

                $lesson('Буль логик утга', 'cpp-booleans', <<<'HTMLCONTENT'
                <p>Буль (Boolean) төрөл нь зөвхөн <code>true</code> (1) эсвэл <code>false</code> (0) гэсэн хоёр утгын нэгийг авна.</p>
                <h3>Хаана хэрэглэгддэг вэ?</h3>
                <ul>
                    <li>Нөхцөлт мэдэгдэл (<code>if</code>)-ийн шалгуур утга</li>
                    <li>Харьцуулах операторын үр дүн (жишээ нь <code>5 &gt; 3</code> нь <code>true</code>)</li>
                    <li>Давталтын үргэлжлэх нөхцөл</li>
                </ul>
                <p>Дэлгэцэд хэвлэхэд <code>true</code> нь <code>1</code>, <code>false</code> нь <code>0</code> гэж харагдана.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    bool isCodingFun = true;\n    bool isFishTasty = false;\n    cout << isCodingFun << \" \" << isFishTasty << endl;\n    return 0;\n}"),
            ]),

            $category('Урсгал удирдлага ба Давталт', 'control-flow-loops', 'Кодынхоо гүйцэтгэх дарааллыг нөхцөл, давталтаар удирдах.', [
                $lesson('Нөхцөлт шалгалт', 'cpp-if-else', <<<'HTMLCONTENT'
                <p>Тодорхой логик нөхцөл биелж байгаа эсэхээс хамааран өөр өөр кодын блокийг ажиллуулахад <code>if</code>, <code>else if</code>, <code>else</code> операторуудыг ашиглана.</p>
                <h3>Бүтэц</h3>
                <ul>
                    <li><code>if (нөхцөл) { ... }</code> — эхний нөхцөл</li>
                    <li><code>else if (нөхцөл2) { ... }</code> — нэмэлт нөхцөл, хэдэн ч удаа давтаж болно</li>
                    <li><code>else { ... }</code> — өмнөх бүх нөхцөл худал байх тохиолдол</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int score = 85;\n    if (score >= 90) {\n        cout << \"Onts sursan\";\n    }\n     else if (score >= 70) {\n        cout << \"Sain sursan\";\n    }\n    else {\n        cout << \"Dahin oorolduya\";\n    }\n    return 0;\n}"),

                $lesson('Олон сонголттой нөхцөл', 'cpp-switch', <<<'HTMLCONTENT'
                <p>Нэг хувьсагч олон тодорхой утгатай тэнцэж байгааг шалгахдаа <code>switch</code> ашиглавал <code>if-else</code>-ээс илүү цэгцтэй код болдог.</p>
                <h3>Бүтэц</h3>
                <ul>
                    <li><code>switch (хувьсагч) { ... }</code> — шалгах хувьсагчийг заана</li>
                    <li><code>case утга:</code> — тухайн утгатай тэнцэх тохиолдол</li>
                    <li><code>break;</code> — тухайн case-ээс гарах, дараагийн case уруу "унахаас" сэргийлнэ</li>
                    <li><code>default:</code> — бусад бүх тохиолдол</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int day = 2;\n    switch (day) {\n        case 1: cout << \"Davaa\"; break;\n        case 2: cout << \"Myagmar\"; break;\n        default: cout << \"Busad odor\";\n    }\n    return 0;\n}"),

                $lesson('While ба Do...While давталт', 'cpp-while-loop', <<<'HTMLCONTENT'
                <p>Нөхцөл үнэн байх хугацаанд кодын блокийг дахин дахин ажиллуулна.</p>
                <h3>Хоёр хувилбар</h3>
                <ul>
                    <li><code>while (нөхцөл) { ... }</code> — нөхцөлийг ЭХЭЛЖ шалгаад ажиллана</li>
                    <li><code>do { ... } while (нөхцөл);</code> — эхлээд нэг удаа ажиллаад, дараа нь нөхцөлийг шалгана</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    int i = 0;\n    while (i < 3) {\n        cout << i << \" \";\n        i++;\n    }\n    return 0;\n}"),

                $lesson('For давталт ба Range-based For', 'cpp-for-loop', <<<'HTMLCONTENT'
                <p>Давталтын тоо тодорхой байх үед <code>for</code> давталт ашиглана.</p>
                <h3>Стандарт for давталт</h3>
                <p><code>for (эхлэл; нөхцөл; алхам) { ... }</code></p>
                <h3>Range-based for</h3>
                <p>Массив эсвэл цуглуулгын элемент бүрээр шууд дамжин гарахад ашиглагддаг, илүү товч бичлэг.</p>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    for (int k = 0; k < 5; k++) {\n        cout << k << \"\\n\";\n    }\n    return 0;\n}"),

                $lesson('Break ба Continue', 'cpp-break-continue', <<<'HTMLCONTENT'
                <p>Давталтын явцыг зохицуулах хоёр түлхүүр үг.</p>
                <h3>Ялгаа</h3>
                <ul>
                    <li><code>break;</code> — давталтыг шууд бүрэн зогсоож давталтаас гарна</li>
                    <li><code>continue;</code> — давталтын одоогийн алхмыг алгасаж дараагийн алхам руу шилжинэ</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    for (int i = 0; i < 10; i++) {\n        if (i == 4) { break; }\n        cout << i << \" \";\n    }\n    return 0;\n}"),
            ]),

            $category('Санах ой ба Өгөгдлийн бүтэц', 'memory-data-structures', 'Олон утгыг зэрэг хадгалах, лавлагаа ба заагчаар санах ойд шууд хандах.', [
                $lesson('Массив', 'cpp-arrays', <<<'HTMLCONTENT'
                <p>Массив нь нэг ижил өгөгдлийн төрлийн олон утгыг нэг хувьсагчид дарааллуулан хадгалах бүтэц юм.</p>
                <h3>Онцлог</h3>
                <ul>
                    <li>Индекс <code>0</code>-ээс эхэлдэг</li>
                    <li>Хэмжээ нь тогтмол — зарласны дараа өөрчлөгдөхгүй</li>
                    <li><code>массив[индекс]</code> хэлбэрээр тухайн элементэд хандана</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\n#include <string>\nusing namespace std;\n\nint main() {\n    string cars[3] = {\"Toyota\", \"Nissan\", \"Honda\"};\n    cout << cars[0] << \" \" << cars[1] << \" \" << cars[2];\n    return 0;\n}"),

                $lesson('Лавлагаа хувьсагч', 'cpp-references', <<<'HTMLCONTENT'
                <p>Лавлагаа (Reference) хувьсагч гэдэг нь бэлэн байгаа өөр нэг хувьсагчийн хоёрдогч "алиас" юм. <code>&amp;</code> операторыг ашиглан үүсгэнэ.</p>
                <h3>Гол шинж чанар</h3>
                <ul>
                    <li>Лавлагаа хувьсагчийг өөрчлөхөд эх хувьсагч нь мөн адил өөрчлөгдөнө</li>
                    <li>Үүсгэсний дараа өөр хувьсагч руу дахин "чиглүүлж" (rebind) болохгүй</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\n#include <string>\nusing namespace std;\n\nint main() {\n    string food = \"Huushuur\";\n    string &meal = food;\n    meal = \"Buuz\";\n    cout << food;\n    return 0;\n}"),

                $lesson('Заагч хувьсагч', 'cpp-pointers', <<<'HTMLCONTENT'
                <p>Заагч (Pointer) гэдэг нь өөр хувьсагчийн санах ойн адресыг (Memory Address) өөртөө хадгалдаг тусгай хувьсагч юм.</p>
                <h3>Гол операторууд</h3>
                <ul>
                    <li><code>&amp;</code> — хувьсагчийн санах ойн хаягийг авах</li>
                    <li><code>*</code> — заагч хувьсагч зарлах, эсвэл хаягаар заагдсан утга руу хандах (dereferencing)</li>
                </ul>
                <p>Заагч нь C++-ийг маш хурдан болгодог боловч буруу хаяг руу хандвал програм гацаж нурах (Segmentation Fault) эрсдэлтэй тул анхааралтай ашиглах хэрэгтэй.</p>
                HTMLCONTENT,
                "#include <iostream>\n#include <string>\nusing namespace std;\n\nint main() {\n    string fruit = \"Alim\";\n    string* ptr = &fruit;\n    cout << *ptr;\n    return 0;\n}"),
            ]),

            $category('Функц ба Объект Хандлагат Программчлал', 'functions-oop', 'Кодоо функцэд хуваах, дараа нь классаар загварчлан OOP-той танилцах.', [
                $lesson('Функц ба Параметрүүд', 'cpp-functions', <<<'HTMLCONTENT'
                <p>Функц нь дахин ашиглагдах боломжтой кодын блок юм. Параметр авч, тооцоолол хийн утга буцааж (<code>return</code>) болно.</p>
                <h3>Функцийн бүтэц</h3>
                <ul>
                    <li><code>буцаах_төрөл нэр(параметрүүд) { ... }</code> — функц зарлах</li>
                    <li><code>return утга;</code> — дуудсан газар руугаа утга буцаах</li>
                    <li><code>void</code> буцаах төрлөөр зарласан функц ямар нэг утга буцаахгүй</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint addNumbers(int a, int b) {\n    return a + b;\n}\n\nint main() {\n    cout << \"Niilber: \" << addNumbers(5, 3);\n    return 0;\n}"),

                $lesson('Хувьсагчийн үйлчлэх хүрээ', 'cpp-scope', <<<'HTMLCONTENT'
                <p>Хувьсагч зарлагдсан байрлалаас хамааран хаанаас хандаж болохыг "үйлчлэх хүрээ" (scope) гэнэ.</p>
                <h3>Хоёр төрөл</h3>
                <ul>
                    <li><strong>Локал (Local Scope):</strong> Функц эсвэл кодын блок <code>{}</code> дотор зарлагдсан хувьсагч, зөвхөн тухайн блок дотроо хүчинтэй</li>
                    <li><strong>Глобал (Global Scope):</strong> Бүх функцийн гадна зарлагдсан хувьсагч, програмын хаанаас ч хандаж болно</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint globalCount = 10;\n\nvoid showLocal() {\n    int localCount = 5;\n    cout << \"Local: \" << localCount << endl;\n}\n\nint main() {\n    showLocal();\n    cout << \"Global: \" << globalCount;\n    return 0;\n}"),

                $lesson('Объект Хандлагат Програмчлал', 'cpp-oop', <<<'HTMLCONTENT'
                <p>OOP (Object-Oriented Programming) нь програмыг бодит ертөнцийн объект ба тэдгээрийн хоорондын харилцан үйлчлэл хэлбэрээр загварчлах арга юм.</p>
                <h3>4 тулгуур зарчим</h3>
                <ul>
                    <li><strong>Encapsulation (Капсулжуулалт):</strong> Өгөгдөл ба методыг нэг класс дотор нууцлан багцлах</li>
                    <li><strong>Abstraction (Абстракц):</strong> Нарийн төвөгтэй дотоод бүтцийг нууж, зөвхөн хэрэгцээт интерфейсийг ил харуулах</li>
                    <li><strong>Inheritance (Уламжлал):</strong> Бэлэн классаас шинж чанар, методыг өвлөн авч шинэ класс үүсгэх</li>
                    <li><strong>Polymorphism (Олон хэлбэртэйн шинж):</strong> Нэг нэртэй методыг олон янзаар ажиллуулах боломж</li>
                </ul>
                HTMLCONTENT,
                "#include <iostream>\nusing namespace std;\n\nint main() {\n    cout << \"OOP-iin 4 tulguur: Encapsulation, Abstraction, Inheritance, Polymorphism\";\n    return 0;\n}"),

                $lesson('Класс ба Объект', 'cpp-classes-objects', <<<'HTMLCONTENT'
                <p>Класс гэдэг нь объект үүсгэх загвар (зураг төсөл) юм. Объект нь тухайн загвараар бүтээгдсэн бодит бие юм.</p>
                <h3>private ба public</h3>
                <ul>
                    <li><code>private:</code> — зөвхөн класс дотроо хандах боломжтой гишүүд</li>
                    <li><code>public:</code> — класснаас гадна ч хандаж болох гишүүд, методууд</li>
                </ul>
                <p>Ижил классаас <code>Student s1;</code> хэлбэрээр хэд хэдэн объект үүсгэж, тус бүрд өөр өөр утга хадгалж болно.</p>
                HTMLCONTENT,
                "#include <iostream>\n#include <string>\nusing namespace std;\n\nclass Student {\n    public:\n        string name;\n        void greet() {\n            cout << \"Sain uu, bi \" << name;\n        }\n};\n\nint main() {\n    Student s1;\n    s1.name = \"Bat\";\n    s1.greet();\n    return 0;\n}"),
            ]),
        ];
    }

    private function placeholderImage(string $label, string $hex): string
    {
        [$r, $g, $b] = sscanf($hex, '%02x%02x%02x');

        $image = imagecreatetruecolor(640, 400);
        imagefill($image, 0, 0, imagecolorallocate($image, $r, $g, $b));

        $white = imagecolorallocate($image, 255, 255, 255);
        imagestring($image, 5, 20, 20, $label, $white);

        ob_start();
        imagepng($image);
        $contents = ob_get_clean();
        imagedestroy($image);

        $path = 'projects/'.Str::random(20).'.png';
        Storage::disk('public')->put($path, $contents);

        return $path;
    }
}

# Дизайн-система Ufamasters (legacy-тема Markedia)

Документ описывает внешний вид сайта, восстановленный в Figma и перенесённый на Vite + Tailwind 4 + Alpine.
Источник правды по виду — старая тема (Bootstrap 4 alpha, `resources/assets/front/css`), по макетам — файл Figma
**Legacy Ufamasters** (`cVpIU5zMDXUTryixqevTgA`), по коду — `resources/css/app.css` и компоненты `resources/views/components/ui`.

> Принцип: сначала переносим технологии и сохраняем облик «как был», редизайн — отдельным этапом.

## 1. Сетка и брейкпоинты

| Параметр | Значение |
|---|---|
| Контейнер | 1140 px, внутренние отступы 15 px (рабочая ширина 1110) |
| Колонки на десктопе | контент 730 + сайдбар 350, зазор 30 |
| Полноширинные страницы (прошивки) | `container-fluid`: левая колонка 210, контент остальное, зазор 30 |
| Брейкпоинт перехода | `lg` = 992 px (`--breakpoint-lg: 62rem`), как в Bootstrap 4 |
| Планшет (820) | контейнер 720, рабочая ширина 690, сайдбар под контентом |
| Телефон (390) | контент 360, карточка с внутренним отступом 16 |
| Шапка | 90 px на десктопе (рамка 6 px + линия 1 px внутри), 86 px на планшете и телефоне |

Фон страницы `#f1f1f1`, карточки белые с радиусом 5 px, внутренний отступ карточки контента 48 px (на телефоне 48 / 16).

## 2. Цвета

Переменные Figma (коллекция **Colors**) → токены Tailwind в `resources/css/app.css`.

| Роль | Figma | Значение | Tailwind |
|---|---|---|---|
| Фирменный жёлтый | `brand/yellow` | `#f9ca27` | `brand-400` |
| Жёлтый hover | — | `#e5b50f` | `brand-500` |
| Чёрный (подвал, подшапка, кнопки) | `brand/black` | `#000000` | `footer`, `bg-black` |
| Заголовки, ссылки | `text/ink` | `#111111` | `ink` |
| Основной текст | `text/body` | `#5e5e5e` | `body` |
| Мета, крошки | `text/muted` | `#a3a5a8` | `muted` |
| Категория в мета-блоке | `text/gray` | `#999999` | `text-[#999]` |
| Фон страницы | `surface/page` | `#f1f1f1` | `surface` |
| Белая поверхность | `surface/white` | `#ffffff` | `white` |
| Язычок заголовка (серый) | `surface/tab` | `#edeff2` | `bg-[#edeff2]` |
| Пунктир custombox | `border/dashed` | `#dadada` | `border-[#dadada]` |
| Таблицы | `border/light` | `#eceeef` | `border-[#eceeef]` |
| Рамка подвала, разделители | `border/dashed-dark` | `#333333` | `border-[#333]` |
| Hover ссылок | `link/hover` | `#3197d6` | `link` |
| Telegram | `brand/telegram` | `#0088cc` | `telegram` |
| Шаринг: ВК / Telegram / WhatsApp / ссылка | `share/*` | `#3b5998` / `#00b6f1` / `#25d366` / `#da5333` | `bg-[…]` |
| Плашка «ремонт в день обращения» | `badge/yellow` | `#facc15` | `bg-[#facc15]` |
| Текст виджета сервиса | `text/service`, `text/service-muted` | `#111827`, `#6b7280` | `text-[…]` |
| Рамка поля формы | `form/border` | `#ced4da` | `border-[#ced4da]` |
| Основная синяя кнопка (фильтры) | `brand/action-blue` | `#0275d8` | `bg-[#0275d8]` |
| Строка таблицы (чётная) | `table/stripe` | `#f2f2f2` | `bg-[#f2f2f2]` |
| Ошибка / успех | `state/error`, `state/success` | `#dc3545`, `#28a745` | — |

Экраны входа и регистрации (стиль AdminLTE) используют отдельные цвета: фон `#e9ecef` (`auth/page-bg`), кнопка `#343a40`,
предупреждение `#ffc107`, текст `#212529`, ссылки `#007bff`.

## 3. Типографика

Шрифт всего сайта — **Roboto Slab** 400 и 700 (Google Fonts). Экраны входа — **Source Sans Pro**. Базовый текст 15/26, цвет `body`.

| Стиль Figma | Размер / межстрочный | Начертание | Где |
|---|---|---|---|
| Hero/H1 48 | 48 / 44 | Bold | заголовок hero на десктопе и планшете |
| Hero/H1 34 mobile | 34 / 44 | Bold | заголовок hero на телефоне |
| Article/Title 41 | 41 / 50 | Bold | заголовок статьи и вопроса |
| Post/Title 28 | 28 / 39,2 | Bold | заголовок карточки в ленте |
| Heading/H2 27, H3 22 | 27 / 37,8, 22 / 30,8 | Bold | заголовки внутри текста, карточка поиска |
| Page title 24 | 24 / 24 | Bold | заголовок подшапки |
| Widget title 18 | 18 / 18 | Bold | заголовки виджетов и подвала |
| Lead 16 | 16 / 28 | Regular | подзаголовок hero |
| Body 15 | 15 / 26 | Regular | основной текст |
| List title 15 bold | 15 / 22,5 | Bold | названия в списках виджетов |
| Nav 14 caps | 14 / 14 | Bold, капс | меню |
| Button 14 | 14 / 17,5 | Bold | кнопки |
| Meta 11 caps | 11 / 26 | Bold, капс | дата, автор, просмотры |
| Tag 11 caps | 11 / 26, разрядка 2 px | Regular, капс | теги |
| Breadcrumb 12 | 12 / 26 | Regular | хлебные крошки |
| Caption 10 caps | 10 / 26, разрядка 1 px | Regular, капс | мета в виджетах |

Текст из редактора (статьи, комментарии) оформляется классом `.article-content` в `app.css`: возвращает стили Bootstrap-классов
`img-fluid`, `table`, `table-bordered`, `table-striped`, `table-responsive`, `custombox`.

## 4. Размеры и отступы

Переменные Figma (коллекция **Spacing & Layout**): отступы 4 / 8 / 12 / 16 / 24 / 32 / 48 / 64, радиусы 3 / 4 / 5,
`layout/container` 1140, `layout/content-col` 730, `layout/sidebar-col` 350, `layout/gutter` 30, `layout/header-height` 90.

Ключевые значения: кнопка — отступы 12,8 × 32, углы прямые; кнопка шаринга — высота 40, радиус 3; виджет — отступы 48 × 32,
радиус 5, интервал между виджетами 48; поле формы — высота 38 (в hero 56), радиус 4; тег — высота 28, радиус 3.

## 5. Компоненты

Имена Figma → Blade-компоненты (`resources/views/components/ui`, partials в `resources/views/public/partials`).

| Figma | Варианты | Код |
|---|---|---|
| Button | Black, Yellow, Blue (Telegram), Action blue | `<x-ui.button variant="…">` |
| Share button | сеть × «с подписью» / «только иконка» | `<x-ui.share>` |
| Pagination item | Default, Active, Disabled | `tw/partials/pagination.blade.php` |
| Tag, Category chip | — | `<x-ui.tag-cloud>`, чип в странице статьи |
| Breadcrumbs | — | `<x-ui.breadcrumbs>` |
| Header | Guest, Auth; Header compact (до 992) | `tw/partials/header.blade.php` |
| Hero, Hero search card | десктоп; Hero stacked (планшет, телефон) | `tw/posts/index.blade.php` |
| Page title bar | десктоп; compact (планшет, телефон, без крошек) | `<x-ui.page-header>` |
| Post card | категория показана / скрыта; Post card responsive | `<x-ui.post-card>`, `<x-ui.post-list>` |
| Widgets | Telegram, Popular posts, Categories, Repair at home, Advertising | `tw/partials/widgets/*` |
| Footer | десктоп; Footer stacked | `tw/partials/footer.blade.php` |
| Comment, Comment form, Related posts, Section tab | серый / чёрный язычок | `tw/partials/comments.blade.php`, `<x-ui.legend>` |
| Form field | Text, Select, Textarea; Error | формы вопросов, профиля, фильтров прошивок |
| Data table | каталог прошивок, серии, метаданные, параметры | страницы прошивок |
| Auth card | Login, Register, Forgot, Reset | отдельный layout входа (AdminLTE) |
| Modal | Avatar, Image, Consent | модальные окна |
| Toast | Success, Error | уведомления SweetAlert2 |
| Nav item | Default, Hover, Active (цвет `#3197d6`) | меню шапки |

## 6. Страницы в Figma

Лист `Pages`: десктоп 1440 (ряд y=0 и ряд y=12000), планшет 820 и телефон 390 (ряды y=4500 и y=16000).
Покрыты: лента, статья, категории и категория, теги и тег, вопросы (список, вопрос, создание), «О нас», профиль,
каталог и хаб марки, страница прошивки, «Техника», вход, регистрация, забыли и сброс пароля, подтверждение почты,
модальные окна, уведомления, письмо.

## 7. Что не восстановлено полностью

- Фото hero, миниатюры, обложки и логотип сервиса в Figma — плейсхолдеры; логотип воссоздан фигурами (исходник PNG 200×50, нужен SVG).
- Письмо в Figma набрано Roboto Slab (Times New Roman в Figma недоступен).
- Состояния наведения и фокуса, кроме меню и поля формы с ошибкой, не нарисованы.
- Большая часть адаптивных макетов и статья с комментариями не просмотрены глазами после сборки (проверено автоматически: выходов за границы нет).

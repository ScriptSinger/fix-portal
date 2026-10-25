// Проверка хлебных крошек на всех типах страниц: node tools/check-breadcrumbs.mjs [адрес сайта]
// Нужны playwright-core и Chrome (путь в executablePath). Правило описано в docs/breadcrumbs-audit.md.
import { chromium } from 'playwright-core';
const base = process.argv[2] || 'http://localhost';
const URLS = { 'Лента': '/articles', 'Статья': '/articles/rukovodstvo-po-remontu-holodilnikov-haier-chast-2', 'Категория': '/categories/remont-holodilnikov', 'Теги': '/tags', 'Тег': '/tag/bosch', 'Вопросы': '/questions', 'Вопрос': '/questions/holodilnik-ban-35-fnf-d-c00294677-nuzhna-pomoshch-v-proshivki-i-sam-damp', 'Техника': '/appliances/morozilniki', 'Прошивки': '/firmwares', 'Марка': '/firmwares/indesit', 'Серия': '/firmwares/indesit/wisl', 'Прошивка': '/firmwares/12740', 'Прошивка с маркой': '/firmwares/12730', 'О нас': '/about', 'Согласие': '/personal-data-consent' };
const b = await chromium.launch({ executablePath: '/usr/bin/google-chrome', args: ['--no-sandbox'] });
let bad = 0;
for (const w of [1440, 390]) {
  const p = await (await b.newContext({ viewport: { width: w, height: 900 } })).newPage();
  for (const [name, u] of Object.entries(URLS)) {
    await p.goto(base + u, { waitUntil: 'domcontentloaded' }); await p.waitForTimeout(150);
    const r = await p.evaluate(() => {
      const bar = document.querySelector('[data-breadcrumbs]');
      const vis = bar ? [...bar.querySelectorAll('ol li')].map(li => li.innerText.replace('›', '').trim()) : [];
      const lds = [...document.querySelectorAll('script[type="application/ld+json"]')].map(s => JSON.parse(s.textContent)).filter(j => j['@type'] === 'BreadcrumbList');
      const ld = lds[0]?.itemListElement?.map(i => i.name) || [];
      const h1 = document.querySelector('h1')?.innerText.trim();
      const hdr = document.querySelector('header').getBoundingClientRect();
      return { vis, ld, nLd: lds.length, h1, top: bar ? Math.round(bar.getBoundingClientRect().top - hdr.bottom) : null, w: bar ? Math.round(bar.getBoundingClientRect().width) : 0, scrollW: document.documentElement.scrollWidth, last: bar?.querySelector('[aria-current]')?.getAttribute('title'), urlLast: lds[0]?.itemListElement?.at(-1)?.item };
    });
    const problems = [];
    if (name === 'Лента') { if (r.vis.length) problems.push('на ленте крошки не нужны'); }
    else {
      if (r.nLd !== 1) problems.push(`BreadcrumbList: ${r.nLd}`);
      if (JSON.stringify(r.vis) !== JSON.stringify(r.ld)) problems.push('видимое не равно разметке');
      if (r.vis[0] !== 'Главная') problems.push('первое звено не Главная');
      if (r.last && r.h1 && !(r.last === r.h1 || r.last.includes(r.h1.slice(0, 20)) || r.h1.includes(r.last.slice(0, 20)) || /Прошивки|Согласие|Категории|Теги|Вопросы|О нас/.test(r.last))) problems.push(`последнее звено «${r.last}» не совпадает с h1 «${r.h1}»`);
      if (r.top !== 0) problems.push(`полоса не сразу под шапкой (отступ ${r.top})`);
      if (r.scrollW > w) problems.push('горизонтальная прокрутка');
    }
    if (problems.length) { bad++; console.log('✗', w, name.padEnd(18), problems.join('; ')); }
    else if (w === 1440) console.log('✓', name.padEnd(18), r.vis.join(' › ') || '(нет)');
  }
}
console.log(bad ? `${bad} проблем` : 'все страницы соответствуют правилу');
await b.close();

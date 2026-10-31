# Browser E2E: редактирование обсуждения

Для запуска нужен тестовый пользователь и заранее созданный собственный комментарий или ответ. Сценарий изменяет сообщение, поэтому используйте только отдельное тестовое окружение.

```bash
E2E_BASE_URL=http://localhost \
E2E_EMAIL=e2e@example.test \
E2E_PASSWORD='password' \
E2E_DISCUSSION_URL=/questions/example-question \
E2E_MESSAGE_TEXT='Сообщение для E2E' \
E2E_UPDATED_TEXT='Сообщение после E2E https://example.com' \
E2E_COMMENT_ID=123 \
E2E_OLD_COMMENT_ID=124 \
E2E_FOREIGN_COMMENT_ID=125 \
npm run test:e2e
```

По умолчанию используется `/usr/bin/google-chrome`. Другой путь задаётся через `E2E_BROWSER_PATH`.

Проверяются desktop и mobile viewport, меню действий, открытие редактора, загрузка изображения, AJAX PUT, JSON-ответ, linkify, ошибки 403/422 и удаление сообщения.

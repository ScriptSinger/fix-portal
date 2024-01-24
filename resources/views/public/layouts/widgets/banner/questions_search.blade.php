<div class="newsletter-widget text-center align-self-center">
    <h3>Поиск вопросов</h3>
    <p>Возможно, уже есть ответы на ваш вопрос. Попробуйте воспользоваться поиском</p>
    <form class="form-inline" method="GET" action="{{ route('questions.index') }}">
        <input name="text" class="form-control mr-sm-2 @error('text') has-error @enderror" type="text"
            placeholder="Найти ответ...">
        <button class="btn btn-default btn-block" type="submit">Найти</button>
    </form>
</div>

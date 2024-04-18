<div class="newsletter-widget text-center align-self-center">
    <h3>Поиск статей</h3>
    <p>Возможно, нужная информация уже есть в статьях. Попробуйте поиск</p>

    <form class="form-inline" method="GET" action="{{ route('articles.index') }}">
        <input name="text" class="form-control mr-sm-2 @error('title') has-error @enderror" type="text"
            placeholder="Что вы хотели бы узнать?">
        <button role="button" class="btn btn-dark btn-block" type="submit">Найти</button>
    </form>

</div><!-- end newsletter -->

<div class="btn-group">
    @dump("${row . id}")
    <a type="button" class="btn btn-default" href="{{ route('categories.show', [$category->id]) }}">
        {{ $category->title }}</a>
    <button type="button" class="btn btn-default dropdown-toggle dropdown-icon" data-toggle="dropdown"
        aria-expanded="false">
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <div class="dropdown-menu" role="menu">
        <a class="dropdown-item" href="{{ route('categories.edit', ['category' => $category->id]) }}"><i
                class="fas fa-edit"></i> Редактировать</a>

        <div class="dropdown-divider"></div>
        <form action="{{ route('categories.destroy', ['category' => $category->id]) }}" method="POST">
            @csrf
            @method('DELETE')
            <button class="dropdown-item" type="submit" class=""
                onclick="return confirm('Подтвердите удаление')">
                <i class="fas fa-trash"></i> Удалить
            </button>
        </form>
    </div>
</div>

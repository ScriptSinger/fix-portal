<div class="post-sharing">
    <ul class="list-inline">
        <li class="mb-2"><a href="https://vk.com/share.php?url={{ route('articles.show', ['article' => $reference]) }}"
                id="vk_share_button" class="fb-button btn btn-primary" target="_blank"><i class="fa fa-vk"></i>
                <span class="down-mobile">Поделиться</span></a></li>

        <li class="mb-2"><a
                href="https://telegram.me/share/url?url={{ route('articles.show', ['article' => $reference]) }}"
                class="tw-button btn btn-primary" target="_blank"><i class="fa fa-telegram"></i>
                <span class="down-mobile">Поделиться</span></a></li>

        <li class="mb-2"><a
                href="https://web.whatsapp.com/send?text={{ route('articles.show', ['article' => $reference]) }}"
                class="whatsapp-button btn btn-primary" target="_blank"><i class="fa fa-whatsapp"></i></a>
        </li>
        <li class="mb-2"><a data-url="{{ route('articles.show', ['article' => $reference]) }}" href="#"
                class="gp-button btn btn-primary copyLink"><i class="fa fa-link"></i></a>
        </li>
    </ul>
</div>

@pushonce('scripts', 'sharing')
    <script src="{{ asset('assets/front/js/custom/sweetAlert2/sharing.js') }}"></script>
@endpushonce

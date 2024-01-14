<script>
    document.addEventListener('DOMContentLoaded', function() {
        document.addEventListener('submit', function(e) {
            if (e.target.matches('.like-form')) {
                e.preventDefault();

                var button = e.submitter;
                var action = e.target.getAttribute('data-' + button.dataset.type + '-route');

                var csrf = document.querySelector('input[name="_token"]').value;


                var likeCountSpan = e.target.querySelector('.like-count');
                var dislikeCountSpan = e.target.querySelector('.dislike-count');

                var xhr = new XMLHttpRequest();
                xhr.open('POST', action, true);
                xhr.setRequestHeader('Content-Type', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrf);

                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        var response = JSON.parse(xhr.responseText);
                        var newLikeCount = response.likeCount;
                        var newDislikeCount = response.dislikeCount;


                        if (likeCountSpan) {
                            likeCountSpan.textContent = newLikeCount;
                        }


                        if (dislikeCountSpan) {
                            dislikeCountSpan.textContent = newDislikeCount;
                        }
                    }
                };
                xhr.send();
            }
        });
    });
</script>

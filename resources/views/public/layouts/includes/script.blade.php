<script src="{{ asset('assets/front/js/main.js') }}"></script>
@if (session('success'))
    <script>
        const Toast = Swal.mixin({
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer)
                toast.addEventListener('mouseleave', Swal.resumeTimer)
            }
        })
        Toast.fire({
            icon: 'success',
            title: 'Успешно!',
            text: "{{ session('success') }}",
        })
        // Добавляем код для закрытия сообщения через 3 секунды
        setTimeout(() => {
            Toast.close();
        }, 3000);
    </script>
@endif

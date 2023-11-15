<script src="{{ asset('assets/admin/js/adminlte.js') }}"></script>
<script>
    $('.nav-sidebar a').each(function() {
        let location = window.location.protocol + '//' + window.location.host + window.location.pathname;
        let link = this.href;
        if (link == location) {
            $(this).addClass('active');
            $(this).closest('.has-treeview').addClass('menu-open');
        }
    });

    $(function() {
        bsCustomFileInput.init();
    });
</script>
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
@if (session('error'))
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
            icon: 'error',
            title: 'Ошибка!',
            text: "{{ session('error') }}",
        })
        // Добавляем код для закрытия сообщения через 3 секунды
        setTimeout(() => {
            Toast.close();
        }, 3000);
    </script>
@endif

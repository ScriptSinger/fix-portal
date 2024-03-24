  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="{{ url('/') }}" target="_blank" class="brand-link">
          <img src="{{ asset('assets/admin/img/AdminLTELogo.png') }}" alt="Logo"
              class="brand-image img-circle elevation-3" style="opacity: .8">
          <span class="brand-text font-weight-light">{{ config('app.name', 'Laravel') }}</span>
      </a>

      <!-- Sidebar -->
      <div class="sidebar">
          <!-- Sidebar user (optional) -->

          <div class="user-panel mt-3 pb-3 mb-3 d-flex">
              @auth
                  <div class="image">
                      <img src="{{ asset('assets/admin/img/user2-160x160.jpg') }}" class="img-circle elevation-2"
                          alt="User Image">
                  </div>
                  <div class="info">
                      <a href="#" class="d-block">{{ auth()->user()->name }}</a>
                  </div>
              @endauth
          </div>

          <!-- SidebarSearch Form -->
          <div class="form-inline">
              <div class="input-group" data-widget="sidebar-search">
                  <input class="form-control form-control-sidebar" type="search" placeholder="Search"
                      aria-label="Search">
                  <div class="input-group-append">
                      <button class="btn btn-sidebar">
                          <i class="fas fa-search fa-fw"></i>
                      </button>
                  </div>
              </div>
          </div>

          <!-- Sidebar Menu -->
          <nav class="mt-2">
              <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                  data-accordion="false">
                  <!-- Add icons to the links using the .nav-icon class
               with font-awesome or any other icon font library -->
                  <li class="nav-item">
                      <a href="{{ route('admin.index') }}" class="nav-link">
                          <i class="nav-icon fas fa-home"></i>
                          <p>
                              Главная
                              <span class="right badge badge-danger">New</span>
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('custom.edit') }}" class="nav-link">
                          <i class="nav-icon fas fa-cog"></i>
                          <p>
                              Кастомизация
                          </p>
                      </a>
                  </li>
                  <li class="nav-item">
                      <a href="{{ route('admin.logs.index') }}" class="nav-link">
                          <i class="nav-icon fas fa-history"></i>
                          <p>
                              Логи
                          </p>
                      </a>
                  </li>
                  <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-users"></i>
                          <p>
                              Сообщество
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">

                          <li class="nav-item">
                              <a href="{{ route('admin.users.index') }}" class="nav-link">
                                  <i class="fa fa-user nav-icon"></i>
                                  <p>Пользователи</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.comments.index') }}" class="nav-link">
                                  <i class="fa fa-comment nav-icon"></i>
                                  <p>Комментарии</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.replies.index') }}" class="nav-link">
                                  <i class="fa fa-comments nav-icon"></i>
                                  <p>Ответы</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-pen-square"></i>
                          <p>
                              Контент
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.posts.index') }}" class="nav-link">
                                  <i class="fa fa-file-alt nav-icon"></i>
                                  <p>Статьи</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.categories.index') }}" class="nav-link">
                                  <i class="fa fa-folder nav-icon"></i>
                                  <p>Категории</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.tags.index') }}" class="nav-link">
                                  <i class="fa fa-tags nav-icon"></i>
                                  <p>Метки</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item has-treeview">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-bullhorn"></i>
                          <p>
                              Форум
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.appliances.index') }}" class="nav-link">
                                  <i class="fa fa-plug nav-icon"></i>
                                  <p>Приборы</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.questions.index') }}" class="nav-link">
                                  <i class="fa fa-question nav-icon"></i>
                                  <p>Вопросы</p>
                              </a>
                          </li>

                      </ul>
                  </li>
                  <li class="nav-header">ХРАНИЛИЩЕ</li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-wave-square"></i>
                          <p>
                              Прошивки
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.firmwares.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                      </ul>
                  </li>

                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-images"></i>
                          <p>
                              Изображения
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.images.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.images.grid') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Сетка</p>
                              </a>
                          </li>
                      </ul>
                  </li>

              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>

  @push('scripts')
      <script src="{{ asset('assets/admin/js/custom/adminlte/sidebar.js') }}"></script>
  @endpush

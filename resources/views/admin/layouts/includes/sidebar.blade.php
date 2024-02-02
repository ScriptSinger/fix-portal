  <!-- Main Sidebar Container -->
  <aside class="main-sidebar sidebar-dark-primary elevation-4">
      <!-- Brand Logo -->
      <a href="{{ url('/') }}" target="_blank" class="brand-link">
          <img src="{{ asset('assets/admin/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
              class="brand-image img-circle elevation-3" style="opacity: .8">
          <span class="brand-text font-weight-light">AdminLTE 3</span>
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
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-users"></i>
                          <p>
                              Пользователи
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.users.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.users.create') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Добавить</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-plug"></i>
                          <p>
                              Приборы
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.appliances.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.appliances.create') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Добавить</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-question"></i>
                          <p>
                              Вопросы
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.questions.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                      </ul>
                  </li>

                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-archive"></i>
                          <p>
                              Категории
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.categories.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.categories.create') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Добавить</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-tags"></i>
                          <p>
                              Метки
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.tags.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.tags.create') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Добавить</p>
                              </a>
                          </li>
                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-edit"></i>
                          <p>
                              Статьи
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.posts.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>
                          <li class="nav-item">
                              <a href="{{ route('admin.posts.create') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Добавить</p>
                              </a>
                          </li>
                      </ul>
                  </li>
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
                          {{-- <li class="nav-item">
                            <a href="{{ route('settings.create') }}" class="nav-link">
                                <i class="far fa-circle nav-icon"></i>
                                <p>Добавить</p>
                            </a>
                        </li> --}}

                      </ul>
                  </li>
                  <li class="nav-item">
                      <a href="#" class="nav-link">
                          <i class="nav-icon fas fa-comments"></i>
                          <p>
                              Коментарии
                              <i class="right fas fa-angle-left"></i>
                          </p>
                      </a>
                      <ul class="nav nav-treeview">
                          <li class="nav-item">
                              <a href="{{ route('admin.comments.index') }}" class="nav-link">
                                  <i class="far fa-circle nav-icon"></i>
                                  <p>Список</p>
                              </a>
                          </li>

                      </ul>
                  </li>

                  <li class="nav-header">EXAMPLES</li>
              </ul>
          </nav>
          <!-- /.sidebar-menu -->
      </div>
      <!-- /.sidebar -->
  </aside>

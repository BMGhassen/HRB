<nav class="navbar navbar-header navbar-header-transparent navbar-expand-lg border-bottom">
    <div class="container-fluid">
        <div class="navbar-nav ms-auto align-items-center">
            <ul class="navbar-nav topbar-nav ms-md-auto align-items-center">
            <li class="nav-item topbar-icon dropdown hidden-caret submenu">
            <a class="nav-link dropdown-toggle show" href="#" id="notifDropdown" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                <i class="fa fa-bell"></i>
                <span class="notification" id="notification-count">{{ $unreadNotificationsCount }}</span>
            </a>
            <ul class="dropdown-menu notif-box animated fadeIn" aria-labelledby="notifDropdown">


                @foreach ($unreadNotifications as $notification)
                <li>
                    <div class="dropdown-title">
                      You have 4 new notification
                    </div>
                  </li>
                  <li>
                    <div class="notif-scroll scrollbar-outer">
                      <div class="notif-center">
                        <a href="{{ route('notifications.markAsRead', $notification->id) }}">
                          <div class="notif-icon notif-primary">
                            <i class="fa fa-user-plus"></i>
                          </div>
                          <div class="notif-content">
                            <span class="block"> Nouvel utilisateur </span>
                            <span class="time">{{ $notification->data['name'] }}</span>
                          </div>
                        </a>
                  </li>
                @endforeach

              </ul>

            </li>
            <li class="nav-item topbar-icon dropdown hidden-caret submenu">
            <a class="button btn btn-danger" href="{{ route('logout') }}"
                onclick="event.preventDefault();
                document.getElementById('logout-form').submit();">
                    {{ __('Déconnection ') }}
            </a>
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            </li>
        </div>
    </div>
</nav>

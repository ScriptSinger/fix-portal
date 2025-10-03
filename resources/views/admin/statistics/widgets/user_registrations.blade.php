<div class="col-lg-3 col-6">
    <div class="small-box bg-danger">
        <div class="overlay dark">
            <i class="fas fa-3x fa-sync-alt"></i>
        </div>
        <div class="inner">
            <h3>
                <span data-route="{{ route('api.statistics.index') }}" data-statistic-key="userRegistrations">0</span>
                /
                <span data-route="{{ route('api.statistics.index') }}" data-statistic-key="verifiedUsersCount">0</span>
            </h3>

            <p>User Registrations</p>
        </div>
        <div class="icon">
            <i class="fas fa-users"></i>
        </div>
        <a href="{{ route('admin.users.index') }}" class="small-box-footer">More info <i
                class="fas fa-arrow-circle-right"></i></a>
    </div>
</div>
<?php
/**
 * Sidebar partial
 */
?>
<nav class="sidebar sidebar-offcanvas" id="sidebar">
    <ul class="nav ">
        <li class="nav-item nav-category">Main</li>
        <li class="nav-item <?= is_current_url('') || is_current_url('home') || is_current_url('home/index') ? 'active' : ''; ?>">
            <a class="nav-link" href="<?= base_url(); ?>">
                <span class="icon-bg"><i class="mdi mdi-cube menu-icon"></i></span>
                <span class="menu-title">Dashboard</span>
            </a>
        </li>

        <li class="nav-item nav-category">QA Evaluation</li>

        <li class="nav-item <?= is_current_url('businessUnit') ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#businessUnits" aria-expanded="<?= is_current_url('businessUnit') ? 'true' : 'false'; ?>" aria-controls="businessUnits">
                <span class="icon-bg"><i class="mdi mdi-office-building menu-icon"></i></span>
                <span class="menu-title">Business Units</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('businessUnit') ? 'show' : ''; ?>" id="businessUnits">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('businessUnit') && !is_current_url('businessUnit/create') ? 'active' : ''; ?>" href="<?= base_url('businessUnit'); ?>">View Business Units</a></li>
                    <?php if (current_user()['role'] === 'admin'): ?>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('businessUnit/create') ? 'active' : ''; ?>" href="<?= base_url('businessUnit/create'); ?>">Add Business Unit</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <li class="nav-item <?= is_current_url('activity') ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#activities" aria-expanded="<?= is_current_url('activity') ? 'true' : 'false'; ?>" aria-controls="activities">
                <span class="icon-bg"><i class="mdi mdi-briefcase menu-icon"></i></span>
                <span class="menu-title">Activities</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('activity') ? 'show' : ''; ?>" id="activities">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('activity') && !is_current_url('activity/create') ? 'active' : ''; ?>" href="<?= base_url('activity'); ?>">View Activities</a></li>
                    <?php if (current_user()['role'] === 'admin'): ?>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('activity/create') ? 'active' : ''; ?>" href="<?= base_url('activity/create'); ?>">Add Activity</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </li>

        <li class="nav-item <?= is_current_url('agent') ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#agents" aria-expanded="<?= is_current_url('agent') ? 'true' : 'false'; ?>" aria-controls="agents">
                <span class="icon-bg"><i class="mdi mdi-account-multiple menu-icon"></i></span>
                <span class="menu-title">Agents</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('agent') ? 'show' : ''; ?>" id="agents">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('agent') && !is_current_url('agent/create') ? 'active' : ''; ?>" href="<?= base_url('agent'); ?>">View Agents</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('agent/create') ? 'active' : ''; ?>" href="<?= base_url('agent/create'); ?>">Add Agent</a></li>
                </ul>
            </div>
        </li>

        <?php if (current_user()['role'] === 'admin'): ?>
        <li class="nav-item <?= is_current_url('evaluation_template') ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#templates" aria-expanded="<?= is_current_url('evaluation_template') ? 'true' : 'false'; ?>" aria-controls="templates">
                <span class="icon-bg"><i class="mdi mdi-file-document-edit menu-icon"></i></span>
                <span class="menu-title">Templates</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('evaluation_template') ? 'show' : ''; ?>" id="templates">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('evaluation_template') && !is_current_url('evaluation_template/create') ? 'active' : ''; ?>" href="<?= base_url('evaluation_template'); ?>">View Templates</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('evaluation_template/create') ? 'active' : ''; ?>" href="<?= base_url('evaluation_template/create'); ?>">Add Template</a></li>
                </ul>
            </div>
        </li>
        <?php endif; ?>

        <li class="nav-item <?= is_current_url('evaluation') ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#evaluations" aria-expanded="<?= is_current_url('evaluation') ? 'true' : 'false'; ?>" aria-controls="evaluations">
                <span class="icon-bg"><i class="mdi mdi-clipboard-check menu-icon"></i></span>
                <span class="menu-title">Evaluations</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('evaluation') ? 'show' : ''; ?>" id="evaluations">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('evaluation') && !is_current_url('evaluation/create') ? 'active' : ''; ?>" href="<?= base_url('evaluation'); ?>">View Evaluations</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('evaluation/create') ? 'active' : ''; ?>" href="<?= base_url('evaluation/create'); ?>">New Evaluation</a></li>
                </ul>
            </div>
        </li>

        <li class="nav-item <?= is_current_url('report')    ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#reports" aria-expanded="<?= is_current_url('report')  ? 'true' : 'false'; ?>" aria-controls="reports">
                <span class="icon-bg"><i class="mdi mdi-chart-bar menu-icon"></i></span>
                <span class="menu-title">Reports</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('report')  ? 'show' : ''; ?>" id="reports">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('report') && !is_current_url('report/agent') && !is_current_url('report/activity') && !is_current_url('report/business_unit') ? 'active' : ''; ?>" href="<?= base_url('report'); ?>">Dashboard</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('report/agent') ? 'active' : ''; ?>" href="<?= base_url('report/agent'); ?>">Agent Reports</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('report/activity') ? 'active' : ''; ?>" href="<?= base_url('report/activity'); ?>">Activity Reports</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('report/business_unit') ? 'active' : ''; ?>" href="<?= base_url('report/business_unit'); ?>">Business Unit Reports</a></li>

                </ul>
            </div>
        </li>

        <?php if (current_user()['role'] === 'admin'): ?>
        <li class="nav-item <?= is_current_url('admin')  ? 'active' : ''; ?>">
            <a class="nav-link" data-toggle="collapse" href="#admin" aria-expanded="<?= is_current_url('admin') ? 'true' : 'false'; ?>" aria-controls="admin">
                <span class="icon-bg"><i class="mdi mdi-shield-account menu-icon"></i></span>
                <span class="menu-title">Administration</span>
                <i class="menu-arrow"></i>
            </a>
            <div class="collapse <?= is_current_url('admin')  ? 'show' : ''; ?>" id="admin">
                <ul class="nav flex-column sub-menu">
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('admin') ? 'active' : ''; ?>" href="<?= base_url('admin'); ?>">  Dashboard</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('admin/users') || is_current_url('admin/edit_user') || is_current_url('admin/assign_businessUnits') ? 'active' : ''; ?>" href="<?= base_url('admin/users'); ?>">Manage Users</a></li>
                    <li class="nav-item"> <a class="nav-link <?= is_current_url('admin/create_user') ? 'active' : ''; ?>" href="<?= base_url('admin/create_user'); ?>">Create User</a></li>
                </ul>
            </div>
        </li>
        <?php endif; ?>

        <li class="nav-item sidebar-user-actions">
            <div class="user-details">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="d-flex align-items-center">
                            <div class="sidebar-profile-img">
                                <img src="<?= asset_url('images/agent.png'); ?>" alt="image">
                            </div>
                            <div class="sidebar-profile-text">
                                <p class="mb-1"><?= e(current_user()['name']); ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li class="nav-item sidebar-user-actions">
            <div class="sidebar-user-menu">
                <a href="<?= base_url('user/profile'); ?>" class="nav-link <?= is_current_url('user/profile') ? 'active' : ''; ?>"><i class="mdi mdi-settings menu-icon"></i>
                    <span class="menu-title">Profile</span>
                </a>
            </div>
        </li>
        <li class="nav-item sidebar-user-actions">
            <div class="sidebar-user-menu">
                <a href="<?= base_url('user/logout'); ?>" class="nav-link <?= is_current_url('user/logout') ? 'active' : ''; ?>"><i class="mdi mdi-logout menu-icon"></i>
                    <span class="menu-title">Log Out</span>
                </a>
            </div>
        </li>
    </ul>
</nav>

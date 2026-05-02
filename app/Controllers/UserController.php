<?php
/**
 * User Controller
 */
class UserController extends Controller {
    private $userModel;

    /**
     * Constructor - Load the user model
     */
    public function __construct() {
        $this->userModel = $this->model('UserModel');
    }

    /**
     * Login method - Display the login form and process login
     *
     * @return void
     */
    public function login() {
        // Check if user is already logged in
        if (is_authenticated()) {
            redirect(base_url('home/index'));
        }
        // debug(password_hash('admin123', PASSWORD_DEFAULT));

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $email = trim($_POST['email']);
            $password = trim($_POST['password']);

            // Check if email exists
            $user = $this->userModel->getSingleBy('email', $email);
            if ($user && password_verify($password, $user['password'])) {
                // Set session variables
                session_set('user_id', $user['id']);
                session_set('user', $user);

                // Redirect to dashboard
                flash('success', 'You are now logged in');
                redirect(base_url('home/index'));
            } else {
                // Invalid credentials
                flash('error', 'Invalid email or password');

                // Load the login view with error
                $data = [
                    'title' => 'Login',
                    'email' => $email,
                    'password' => '',
                ];

                $this->view('user/login', $data, false);
            }
        } else {
            // Load the login view
            $data = [
                'title' => 'Login',
                'email' => '',
                'password' => '',
            ];

            $this->view('user/login', $data, false);
        }
    }



    /**
     * Logout method - Log the user out
     *
     * @return void
     */
    public function logout() {
        // Unset session variables
        session_remove('user_id');
        session_remove('user');

        // Destroy session
        session_destroy();

        // Redirect to login page
        redirect(base_url('user/login'));
    }

    /**
     * Profile method - Display and update user profile
     *
     * @return void
     */
    public function profile() {
        // Check if user is authenticated
        if (!is_authenticated()) {
            redirect(base_url('user/login'));
        }

        $user = current_user();

        // Check if form is submitted
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate form data
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $current_password = trim($_POST['current_password']);
            $new_password = trim($_POST['new_password']);
            $confirm_password = trim($_POST['confirm_password']);

            // Check if name is empty
            if (empty($name)) {
                flash('error', 'Name is required');

                $data = [
                    'title' => 'User Profile',
                    'user' => $user
                ];

                $this->view('user/profile', $data);
                return;
            }

            // Check if email is empty
            if (empty($email)) {
                flash('error', 'Email is required');

                $data = [
                    'title' => 'User Profile',
                    'user' => $user
                ];

                $this->view('user/profile', $data);
                return;
            }

            // Check if email is valid
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                flash('error', 'Invalid email format');

                $data = [
                    'title' => 'User Profile',
                    'user' => $user
                ];

                $this->view('user/profile', $data);
                return;
            }

            // Check if email already exists (excluding current user)
            $existingUser = $this->userModel->getByEmail($email);
            if ($existingUser && $existingUser['id'] != $user['id']) {
                flash('error', 'Email already exists');

                $data = [
                    'title' => 'User Profile',
                    'user' => $user
                ];

                $this->view('user/profile', $data);
                return;
            }

            // Prepare update data
            $updateData = [
                'name' => $name,
                'email' => $email
            ];

            // Update password if provided
            if (!empty($current_password) && !empty($new_password)) {
                // Verify current password
                if (!password_verify($current_password, $user['password'])) {
                    flash('error', 'Current password is incorrect');

                    $data = [
                        'title' => 'User Profile',
                        'user' => $user
                    ];

                    $this->view('user/profile', $data);
                    return;
                }

                // Check if password is at least 6 characters
                if (strlen($new_password) < 6) {
                    flash('error', 'Password must be at least 6 characters');

                    $data = [
                        'title' => 'User Profile',
                        'user' => $user
                    ];

                    $this->view('user/profile', $data);
                    return;
                }

                // Check if passwords match
                if ($new_password !== $confirm_password) {
                    flash('error', 'Passwords do not match');

                    $data = [
                        'title' => 'User Profile',
                        'user' => $user
                    ];

                    $this->view('user/profile', $data);
                    return;
                }

                // Hash password
                $updateData['password'] = password_hash($new_password, PASSWORD_DEFAULT);
            }

            // Update user
            $result = $this->userModel->update($user['id'], $updateData);

            if ($result) {
                // Update session
                $updatedUser = $this->userModel->getById($user['id']);
                $_SESSION['user'] = $updatedUser;

                // Set success message
                flash('success', 'Profile updated successfully');

                // Redirect to profile page
                redirect(base_url('user/profile'));
            } else {
                // Set error message
                flash('error', 'Failed to update profile');

                $data = [
                    'title' => 'User Profile',
                    'user' => $user
                ];

                $this->view('user/profile', $data);
            }
        } else {
            // Load the profile view
            $data = [
                'title' => 'User Profile',
                'user' => $user
            ];

            $this->view('user/profile', $data);
        }
    }
}

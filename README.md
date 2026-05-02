<a href="http://www.bootstrapdash.com/demo/connect-plus-free/jquery/template/index.html" target="_blank"><img src="screenshot.jpg"></a>

<h1>Connect Plus PHP MVC Admin Template</h1>
Connect Plus Admin is a free responsive admin template built with Bootstrap 4 and PHP MVC architecture. The template has colorful, attractive yet simple and elegant design. The template is well crafted, with all the components neatly and carefully designed and arranged within the template.

Connect Plus Admin is packed with all the features that fit your needs but not cramped with components you would not even use. It is an excellent fit to build admin panels, e-commerce systems, project management systems, CMS or CRM.

This version has been transformed into a PHP MVC structure with routing and proper separation of concerns.


<h1>Credits:</h1>

- Bootstrap 4
- Material Design Icons
- jQuery
- Gulp
- Chart.js
- jquery-circle-progress

<h1>Browser Support:</h1>

Connect Plus Admin is designed to work flawlessly with all the latest and modern web browsers.

- Chrome (latest)
- FireFox (latest)
- Safari (latest)
- Opera (latest)
- IE10+

<h1>License Information:</h1>


Connect Plus Admin is released under MIT license. Connect Plus Admin is a free Bootstrap 4 admin template developed by BootstrapDash. Feel free to download it, use it, share it, get creative with it.

<h1>Project Structure</h1>

```
project/
├── app/                    # Application code
│   ├── Config/             # Configuration files
│   ├── Controllers/        # Controller classes
│   ├── Core/               # Core framework files
│   ├── Helpers/            # Helper functions
│   ├── Models/             # Model classes
│   └── Views/              # View templates
│       ├── errors/         # Error pages
│       ├── home/           # Home pages
│       ├── layouts/        # Layout templates
│       ├── partials/       # Partial templates
│       └── user/           # User pages
├── assets/                 # Public assets
│   ├── css/                # CSS files
│   ├── images/             # Image files
│   ├── js/                 # JavaScript files
│   ├── scss/               # SCSS files
│   └── vendors/            # Third-party libraries
├── .htaccess               # Apache configuration
├── index.php               # Front controller
└── README.md               # Project documentation
```

<h1>Features</h1>

- MVC architecture
- Routing system
- Database abstraction layer
- Authentication system
- Session management
- Flash messages
- Error handling

<h1>How to use Connect Plus PHP MVC Admin?</h1>

1 - Clone the repository or download as a ZIP file.

2 - Create a database and import the SQL file (if provided).

3 - Configure the database connection in `app/Config/config.php`.

4 - Set the base URL in `app/Config/config.php`.

5 - Make sure the `assets` directory is writable.

6 - Navigate to the project URL in your browser.

<h1>Usage</h1>

<h2>Controllers</h2>

Controllers are located in the `app/Controllers` directory. Each controller should extend the `Controller` class and contain methods that handle specific routes.

Example:

```php
class HomeController extends Controller {
    public function index() {
        $this->view('home/index', ['title' => 'Home']);
    }
}
```

<h2>Models</h2>

Models are located in the `app/Models` directory. Each model should extend the `Model` class and contain methods that interact with the database.

Example:

```php
class UserModel extends Model {
    public function __construct() {
        parent::__construct();
        $this->table = 'users';
    }

    public function getByEmail($email) {
        return $this->getSingleBy('email', $email);
    }
}
```

<h2>Views</h2>

Views are located in the `app/Views` directory. Views are PHP files that contain HTML and PHP code.

Example:

```php
<div class="container">
    <h1><?= $title; ?></h1>
    <p>Welcome to the home page!</p>
</div>
```

<h2>Routes</h2>

Routes are automatically generated based on the controller and method names. For example, the URL `/home/index` will call the `index` method of the `HomeController` class.

<h1>How to Contribute?</h1>

We love your contributions and we welcome them wholeheartedly. We believe the more the merrier.
To contribute:

1. Fork and clone the repository
2. Make your changes
3. Test your changes
4. Submit a pull request

<hr>
Do you need a template with more features and functionalities? Get more with our collection of the premium template with more plugins, eye catching animations, UI components, and sample pages all fitting together with a high-quality design.

Visit <a href="https://www.bootstrapdash.com" target="_blank">https://www.bootstrapdash.com</a> for more admin templates.

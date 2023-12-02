<?php

use App\Entity\user;
use Slim\Views\Twig;
use Slim\Psr7\Request;
use App\Entity\article;
use App\Entity\category;
use Slim\Psr7\Response;
use Slim\Factory\AppFactory;
use Slim\Views\TwigMiddleware;

require_once dirname(__DIR__)."/bootstrap.php";


// Instantiate App
$app = AppFactory::create();
// Create Twig
$twig = Twig::create(dirname(__DIR__).'/templates', ['cache' => false]);

// Add Twig-View Middleware
$app->add(TwigMiddleware::create($app, $twig));

// Add error middleware
$app->addErrorMiddleware(true, true, true);

$categories = $entityManager->getRepository(Category::class)->findAll();
// Add routes
$app->get('/', function (Request $request, Response $response, $args) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'home/home.html.twig', [
        'name' => "Test",
        'categories' => $GLOBALS["categories"]
    ]);
});


$app->get('/articles', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    $articleRepo = $GLOBALS["entityManager"]->getRepository(Article::class);
    return $view->render($response, 'articles/article.html.twig', [
        'articles' => $articleRepo->findAll(),
        'categories' => $GLOBALS["categories"]
    ]);
});
$app->get('/single-article/{slug}', function (Request $request, Response $response, $args) {
    $articleRepo = $GLOBALS["entityManager"]->getRepository(Article::class);
    $view = Twig::fromRequest($request);
    $slug = $args["slug"];
    $article = $articleRepo->findOneBySlug($slug); 

    if (!$article) {
        return $view->render($response, 'errors/error-404.html.twig');
    }

    return $view->render($response, 'single/single_article.html.twig', [
        'article' => $article,
        'categories' => $GLOBALS["categories"]
    ]);
});
$app->get('/article/by/category/{slug}', function (Request $request, Response $response, $args) {
    $categoriesRepo = $GLOBALS["entityManager"]->getRepository(Category::class);
    $articleRepo = $GLOBALS["entityManager"]->getRepository(Article::class);
    $view = Twig::fromRequest($request);
    $slug = $args["slug"];
    $category = $categoriesRepo->findOneBySlug($slug); 

    if (!$category) {
        return $view->render($response, 'errors/error-404.html.twig');
    }

    return $view->render($response, 'articles/article.html.twig', [
        'category' => $category,
        'articles' => $category->getArticles(),
        'categories' => $GLOBALS["categories"]
    ]);
});



$app->get('/users', function (Request $request, Response $response) {
    $view = Twig::fromRequest($request);
    return $view->render($response, 'users/users.html.twig', [
        'users' => []
    ]);
});



$app->get('/hello/{name}', function (Request $request, Response $response, $args) {
    $name = $args['name'];
    $response->getBody()->write("Hello, $name");
    return $response;
});

$app->run();
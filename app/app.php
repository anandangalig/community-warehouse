<?php

    require_once __DIR__."/../vendor/autoload.php";
    require_once __DIR__."/../src/User.php";
    require_once __DIR__."/../src/Item.php";
    require_once __DIR__."/../src/Community.php";

    $app = new Silex\Application();

    $server = "mysql:host=localhost:8889;dbname=warehouse";
    $username = "root";
    $password = "root";
    $DB = new PDO($server, $username, $password);

    $app->register(new Silex\Provider\TwigServiceProvider(), array("twig.path" => __DIR__."/../views"));

    use Symfony\Component\HttpFoundation\Request;
    Request::enableHttpMethodParameterOverride();

    $app->get("/", function() use ($app) {
        return $app["twig"]->render("index.html.twig", array("current_members" => User::getAll()));
    });

//=================Members:====================================
    $app->get("/members", function() use ($app) {
        return $app["twig"]->render("profiles.html.twig", array("current_members" => User::getAll()));
    });


    $app->post("/profile_create", function() use ($app) {
        $name = $_POST['new_member_name'];
        $email = $_POST['new_member_email'];
        $new_member = new User($name, $email);
        $new_member->save();
        return $app["twig"]->render("profiles.html.twig", array("current_members" => User::getAll()));
    });

    $app->get("/delete_all_members", function() use ($app) {
        User::deleteAll();
        return $app["twig"]->render("profiles.html.twig", array("current_members" => User::getAll()));
    });

    $app->get("/member{member_id}", function($member_id) use ($app) {
        $given_member = User::find($member_id);
        return $app["twig"]->render("item.html.twig", array("given_member" => $given_member, "current_communities" => Community::getAll()));
    });


    //===========================Item: =============================================
    $app->get("/items", function() use ($app) {
        return $app["twig"]->render("items.html.twig", array("current_items" => Item::getAll(), "all_members" => User::getAll()));
    });

    $app->post("/item_create", function() use ($app) {
        $owner_id = $_POST['owner_id'];
        $name = $_POST['item_name'];
        $new_item = new Item($owner_id, $name);
        $new_item->save();

        return $app["twig"]->render("items.html.twig", array("current_items" => Item::getAll(), "all_members" => User::getAll()));
    });

    $app->get("/delete_all_items", function() use ($app) {
        Item::deleteAll();
        return $app["twig"]->render("index.html.twig");
    });

    $app->get("/item{item_id}", function($item_id) use ($app) {
        $given_item = Item::find($item_id);
        $item_owner = User::find($given_item->getOwnerId());
        return $app["twig"]->render("item.html.twig", array("all_members" => User::getAll(), "given_item" => $given_item, "item_owner" => $item_owner, "current_items" => Item::getAll()));
    });

    $app->post("/request{item_id}", function($item_id) use ($app) {
        $given_item = Item::find($item_id);
        $item_owner = User::find($given_item->getOwnerId());
        $requestor_id = $_POST['requestor_id'];
        $requestor = User::find($requestor_id);
        $requestor->checkOut($given_item);

        return $app["twig"]->render("request_confirm.html.twig", array("given_item" => $given_item, "item_owner" => $item_owner, "requestor" => $requestor));
    });



    return $app;
    ?>

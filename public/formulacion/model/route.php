<?php

$request = Illuminate\Http\Request::createFromGlobals();

try
{
    $response = $router->dispatch($request);
    $response->send();
}
catch(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $notFound)
{
    with(new \Illuminate\Http\Response('Oops! this page does not exists', 400))->send();
}
?>

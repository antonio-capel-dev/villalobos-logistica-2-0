<?php
// public/index.php

require_once '../app/config/config.php';

// Simple Router for Demo purposes
// In a full MVC this would instantiate Core\App
$request = $_SERVER['REQUEST_URI'];

// Basic static serving for now
// NOTE: For the demo, we just render the home view directly
// Later we will implement the proper Controller dispatching

$pageTitle = 'Inicio';
$viewContent = '../app/views/public/home.php';

require_once '../app/views/layouts/main.php';

<?php

namespace App\Http\Controllers;

use BotMan\BotMan\BotMan;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Conversations\Conversation;
use Illuminate\Http\Request;

class ChatController extends Controller
{

    public function showWidget()
{
    return view('frontend.chatbot');
}


    public function handle()
    {
        $botman = app('botman');
        // Greet
        $botman->hears('hi|hello|hey', function (BotMan $bot) {
            $bot->reply('Hello! 👋 I am your product assistant. You can ask me about our products.');
        });

        // Show products list
        $botman->hears('show products|list products|products', function (BotMan $bot) {
            $bot->reply("Here are our products:\n1. Product A\n2. Product B\n3. Product C\nYou can type 'info [product name]' to know more.");
        });

        // Product info
        $botman->hears('info {product}', function (BotMan $bot, $product) {
            $productInfo = $this->getProductInfo($product);
            $bot->reply($productInfo);
        });

        // Fallback
        $botman->fallback(function (BotMan $bot) {
            $bot->reply("Sorry, I didn't understand that. Try saying 'hi' or 'show products'.");
        });

        $botman->listen();
    }

   
    private function getProductInfo($product)
    {
        $products = [
            'product a' => "Product A: High-quality item, price: $50, available in red and blue.",
            'product b' => "Product B: Premium item, price: $80, comes with a 1-year warranty.",
            'product c' => "Product C: Affordable item, price: $30, best for beginners."
        ];

        $key = strtolower($product);

        return $products[$key] ?? "Sorry, we don't have information about '$product'.";
    }

    public function chatbot()
    {
        return view('frontend.chatbot');
    }
}

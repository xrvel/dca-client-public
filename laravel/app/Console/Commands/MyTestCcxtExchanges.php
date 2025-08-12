<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\MyCcxtExchange;

class MyTestCcxtExchanges extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'my:test-ccxt-exchanges';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test which exchanges are supported by CCXT';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing CCXT Exchange Support...');
        
        try {
            // Check if CCXT is installed
            if (!class_exists('\ccxt\Exchange')) {
                $this->error('CCXT is not installed. Please run: composer require ccxt/ccxt');
                return;
            }
            
            $this->info('CCXT is installed successfully!');
            
            // Get all available exchanges
            $all_exchanges = \ccxt\Exchange::$exchanges;
            $this->info('Total exchanges available in CCXT: ' . count($all_exchanges));
            
            // Check our specific exchanges
            $our_exchanges = [
                'binance', 'kraken', 'bybit', 'indodax', 'okx', 'kucoin', 
                'gate', 'huobi', 'bitfinex', 'bitstamp', 'gemini', 
                'binanceus', 'coinbase', 'bitget', 'mexc', 
                'cryptocom', 'poloniex', 
                'hitbtc', 'coinex',
                'tokocrypto'
            ];
            
            $this->info("\nChecking our supported exchanges:");
            $supported = [];
            $not_supported = [];
            
            foreach ($our_exchanges as $exchange) {
                if (in_array($exchange, $all_exchanges)) {
                    $supported[] = $exchange;
                    $this->info("✅ {$exchange} - SUPPORTED");
                } else {
                    $not_supported[] = $exchange;
                    $this->warn("❌ {$exchange} - NOT SUPPORTED");
                }
            }
            
            $this->info("\n=== SUMMARY ===");
            $this->info("Supported exchanges: " . count($supported));
            $this->info("Not supported exchanges: " . count($not_supported));
            
            if (!empty($supported)) {
                $this->info("\n✅ SUPPORTED EXCHANGES:");
                foreach ($supported as $exchange) {
                    $this->info("  - {$exchange}");
                }
            }
            
            if (!empty($not_supported)) {
                $this->warn("\n❌ NOT SUPPORTED EXCHANGES:");
                foreach ($not_supported as $exchange) {
                    $this->warn("  - {$exchange}");
                }
            }
            
            // Test specific class names that might be different
            $this->info("\n=== TESTING SPECIFIC CLASS NAMES ===");
            $class_tests = [
                'okx' => '\ccxt\okx',
                'kucoin' => '\ccxt\kucoin', 
                'gate' => '\ccxt\gate',
                'huobi' => '\ccxt\huobi',
                'mexc' => '\ccxt\mexc',
                'cryptocom' => '\ccxt\cryptocom',
                'bitfinex' => '\ccxt\bitfinex',
                'bitstamp' => '\ccxt\bitstamp',
                'gemini' => '\ccxt\gemini',
                'tokocrypto' => '\ccxt\tokocrypto',
            ];
            
            foreach ($class_tests as $exchange => $class_name) {
                if (class_exists($class_name)) {
                    $this->info("✅ {$exchange} - Class exists: {$class_name}");
                } else {
                    $this->warn("❌ {$exchange} - Class does not exist: {$class_name}");
                }
            }
            
            $this->info("\nTo see all available exchanges, run:");
            $this->info("php artisan tinker");
            $this->info(">>> \\ccxt\\Exchange::\$exchanges");
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            $this->error('File: ' . $e->getFile() . ':' . $e->getLine());
        }
    }
} 
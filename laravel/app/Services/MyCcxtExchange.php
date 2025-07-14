<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use ccxt\Exchange;
use ccxt\binance;
use ccxt\kraken;
use ccxt\bybit;
use ccxt\indodax;

class MyCcxtExchange
{
    private $exchanges = [];
    private $supported_exchanges = [
        // Verified CCXT Supported Exchanges
        'binance' => binance::class,
        'kraken' => kraken::class,
        'bybit' => bybit::class,
        'indodax' => indodax::class,
        
        // Additional exchanges - will be verified when CCXT is installed
        'bitfinex' => \ccxt\bitfinex::class,
        'bitstamp' => \ccxt\bitstamp::class,
        'binanceus' => \ccxt\binanceus::class,
        'binanceproxy' => \ccxt\binance::class, // Use binance class for proxy
        'coinbase' => \ccxt\coinbase::class,
        'coinbasepro' => \ccxt\coinbasepro::class,
        'bitget' => \ccxt\bitget::class,
        'whitebit' => \ccxt\whitebit::class,
        'bitflyer' => \ccxt\bitflyer::class,
        'liquid' => \ccxt\liquid::class,
        'poloniex' => \ccxt\poloniex::class,
        'bittrex' => \ccxt\bittrex::class,
        'hitbtc' => \ccxt\hitbtc::class,
        'cex' => \ccxt\cex::class,
        'bitmart' => \ccxt\bitmart::class,
        'lbank' => \ccxt\lbank::class,
        'digifinex' => \ccxt\digifinex::class,
        'coinex' => \ccxt\coinex::class,
        'ascendex' => \ccxt\ascendex::class,
        'bigone' => \ccxt\bigone::class,
        'bitforex' => \ccxt\bitforex::class,
        'btcmarkets' => \ccxt\btcmarkets::class,
        'btcturk' => \ccxt\btcturk::class,
    ];

    private $pair_formats = [
        'binance' => 'concat', // BTCUSDT
        'binanceus' => 'concat',
        'bybit' => 'concat',
        'bitget' => 'concat',
        'binanceproxy' => 'concat', // add this line
        'indodax' => 'underscore', // btc_usdt
        'kraken' => 'slash_xbt', // XBT/USDT
        'bitfinex' => 'slash',
        'bitstamp' => 'slash',
        'coinbase' => 'slash',
        'coinbasepro' => 'slash',
        'whitebit' => 'slash',
        'bitflyer' => 'slash',
        'liquid' => 'slash',
        'poloniex' => 'slash',
        'bittrex' => 'slash',
        'hitbtc' => 'slash',
        'cex' => 'slash',
        'bitmart' => 'slash',
        'lbank' => 'slash',
        'digifinex' => 'slash',
        'coinex' => 'slash',
        'ascendex' => 'slash',
        'bigone' => 'slash',
        'bitforex' => 'slash',
        'btcmarkets' => 'slash',
        'btcturk' => 'slash',
    ];

    public function __construct()
    {
        // Initialize supported exchanges
        foreach ($this->supported_exchanges as $name => $class) {
            $this->exchanges[$name] = new $class();
        }
    }

    /**
     * Get all available exchanges in CCXT
     * This method helps verify which exchanges are actually supported
     */
    public function get_all_available_exchanges()
    {
        if (!class_exists('\ccxt\Exchange')) {
            return ['error' => 'CCXT not installed. Run: composer require ccxt/ccxt'];
        }
        
        return \ccxt\Exchange::$exchanges;
    }

    /**
     * Verify if a specific exchange is supported by CCXT
     */
    public function is_exchange_supported($exchange_name)
    {
        if (!class_exists('\ccxt\Exchange')) {
            return false;
        }
        
        $exchange_name = strtolower(trim($exchange_name));
        return in_array($exchange_name, \ccxt\Exchange::$exchanges);
    }

    /**
     * Get verified supported exchanges (only those that exist in CCXT)
     */
    public function get_verified_supported_exchanges()
    {
        if (!class_exists('\ccxt\Exchange')) {
            return ['error' => 'CCXT not installed'];
        }
        
        $verified = [];
        $all_exchanges = \ccxt\Exchange::$exchanges;
        
        foreach ($this->supported_exchanges as $name => $class) {
            if (in_array($name, $all_exchanges)) {
                $verified[$name] = $class;
            }
        }
        
        return $verified;
    }

    public function buy_market($exchange_name, $pair, $amount, $api_key, $api_secret)
    {
        $result = [
            'error' => true,
            'message' => '',
            'data' => [],
        ];

        try {
            $exchange_name = strtolower(trim($exchange_name));
            
            if (!isset($this->supported_exchanges[$exchange_name])) {
                $result['message'] = "Exchange {$exchange_name} is not supported via CCXT";
                return $result;
            }

            $exchange = $this->get_exchange_instance($exchange_name, $api_key, $api_secret);
            
            // Load markets if not loaded
            if (!$exchange->has['fetchMarkets']) {
                $result['message'] = "Exchange {$exchange_name} does not support market fetching";
                return $result;
            }

            $exchange->load_markets();

            // Check if pair exists
            if (!$exchange->has_symbol($pair)) {
                $result['message'] = "Pair {$pair} not found on {$exchange_name}";
                return $result;
            }

            // Get market info
            $market = $exchange->market($pair);
            
            // Determine quote currency (USDT, USD, etc.)
            $quote_currency = $market['quote'];
            
            // Create market buy order
            $order = $exchange->create_market_buy_order($pair, null, [
                'quoteOrderQty' => $amount, // Amount in quote currency
            ]);

            $result['error'] = false;
            $result['message'] = 'Order placed successfully';
            $result['data'] = $order;

        } catch (\Exception $e) {
            $result['message'] = 'CCXT Error: ' . $e->getMessage();
            Log::error('CCXT buy_market error', [
                'exchange' => $exchange_name,
                'pair' => $pair,
                'amount' => $amount,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $result;
    }

    public function check_connection($exchange_name, $api_key, $api_secret)
    {
        try {
            $exchange_name = strtolower(trim($exchange_name));
            
            if (!isset($this->supported_exchanges[$exchange_name])) {
                return [
                    'is_error' => true,
                    'message' => "Exchange {$exchange_name} is not supported via CCXT"
                ];
            }

            $exchange = $this->get_exchange_instance($exchange_name, $api_key, $api_secret);
            
            // Try to fetch balance to test connection
            $balance = $exchange->fetch_balance();
            
            // Get account info if available
            $account_info = 'Connected successfully';
            if (isset($balance['info']['accountType'])) {
                $account_info = 'Account Type: ' . $balance['info']['accountType'];
            } elseif (isset($balance['info']['makerCommission'])) {
                $account_info = 'Maker Commission: ' . $balance['info']['makerCommission'];
            }

            return [
                'is_error' => false,
                'message' => $account_info
            ];

        } catch (\Exception $e) {
            return [
                'is_error' => true,
                'message' => 'Connection failed: ' . $e->getMessage()
            ];
        }
    }

    public function get_supported_exchanges()
    {
        return array_keys($this->supported_exchanges);
    }

    public function is_supported($exchange_name): bool
    {
        $exchange_name = strtolower(trim($exchange_name));
        return isset($this->supported_exchanges[$exchange_name]);
    }

    /**
     * Normalize a trading pair for a given exchange using hardcoded format rules.
     * No API calls are made. This does NOT check if the pair is actually tradable.
     */
    public function normalize_pair($exchange_name, $user_pair)
    {
        $exchange_name = strtolower(trim($exchange_name));
        $user_pair = strtoupper(trim($user_pair));
        $format = $this->pair_formats[$exchange_name] ?? 'slash';

        if ($format === 'concat') {
            // Convert BTC/USDT, BTC-USDT, BTC_USDT to BTCUSDT
            $pair = str_replace(['/', '-', '_'], '', $user_pair);
            return $pair;
        } elseif ($format === 'underscore') {
            // Indodax: BTC/USDT, BTCUSDT, BTC-USDT to btc_usdt
            if (strpos($user_pair, '/') !== false) {
                $pair = str_replace('/', '_', $user_pair);
            } elseif (strpos($user_pair, '-') !== false) {
                $pair = str_replace('-', '_', $user_pair);
            } elseif (strpos($user_pair, '_') !== false) {
                $pair = $user_pair;
            } else {
                $base = substr($user_pair, 0, -4);
                $quote = substr($user_pair, -4);
                $pair = $base . '_' . $quote;
            }
            return strtolower($pair);
        } elseif ($format === 'slash_xbt') {
            // Kraken: BTC/USDT, BTCUSDT, BTC-USDT to XBT/USDT
            if (strpos($user_pair, '/') !== false) {
                $pair = $user_pair;
            } elseif (strpos($user_pair, '-') !== false) {
                $pair = str_replace('-', '/', $user_pair);
            } elseif (strpos($user_pair, '_') !== false) {
                $pair = str_replace('_', '/', $user_pair);
            } else {
                $base = substr($user_pair, 0, -4);
                $quote = substr($user_pair, -4);
                $pair = $base . '/' . $quote;
            }
            return str_replace('BTC', 'XBT', $pair);
        } else {
            // Default: BTCUSDT, BTC-USDT, BTC_USDT to BTC/USDT
            if (strpos($user_pair, '/') !== false) {
                return $user_pair;
            } elseif (strpos($user_pair, '-') !== false) {
                return str_replace('-', '/', $user_pair);
            } elseif (strpos($user_pair, '_') !== false) {
                return str_replace('_', '/', $user_pair);
            } else {
                $base = substr($user_pair, 0, -4);
                $quote = substr($user_pair, -4);
                return $base . '/' . $quote;
            }
        }
    }

    private function get_exchange_instance($exchange_name, $api_key, $api_secret)
    {
        $class = $this->supported_exchanges[$exchange_name];
        $exchange = new $class([
            'apiKey' => $api_key,
            'secret' => $api_secret,
            'sandbox' => false, // Set to true for testing
            'enableRateLimit' => true,
        ]);

        return $exchange;
    }

    public function get_exchange_info($exchange_name)
    {
        try {
            $exchange_name = strtolower(trim($exchange_name));
            
            if (!isset($this->supported_exchanges[$exchange_name])) {
                return null;
            }

            $class = $this->supported_exchanges[$exchange_name];
            $exchange = new $class();
            
            return [
                'name' => $exchange->name,
                'url' => $exchange->urls['www'],
                'has' => $exchange->has,
                'fees' => $exchange->fees,
            ];

        } catch (\Exception $e) {
            Log::error('Error getting exchange info', [
                'exchange' => $exchange_name,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
} 
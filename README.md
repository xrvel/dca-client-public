# DCA Client - Dynamic Crypto Dollar Cost Averaging

A Laravel 12 application for automated cryptocurrency Dollar Cost Averaging (DCA) with dynamic risk-based adjustments. This application automatically buys cryptocurrencies based on risk metrics provided by Alphasquared.io, allowing users to implement sophisticated DCA strategies across multiple exchanges.

## Features

- **Multi-Exchange Support**: Currently supports Indodax and Binance (with proxy support for restricted regions)
- **Dynamic Risk-Based Buying**: Adjusts buy amounts based on real-time risk metrics from Alphasquared.io
- **Multiple API Keys**: Support for multiple exchange accounts per user
- **Flexible Scheduling**: Configurable DCA schedules with various intervals
- **Risk Range Activation**: Set minimum and maximum risk thresholds for buying
- **Multiple Algorithms**: Built-in algorithms for risk-to-amount calculations
- **Comprehensive History**: Track all DCA transactions and performance
- **User Authentication**: Secure user management with Laravel Breeze

## Supported Exchanges

- **Indodax**: Indonesian cryptocurrency exchange
- **Binance**: Global cryptocurrency exchange (with proxy support for restricted regions)

## Risk-Based Algorithms

The application includes several built-in algorithms for calculating buy amounts based on risk metrics:

- **log_1**: Logarithmic algorithm with moderate adjustments
- **log_2**: Logarithmic algorithm with higher multipliers for low risk
- **log_low_1**: Conservative algorithm for low-risk scenarios
- **square_1**: Quadratic-based calculation
- **fixed**: Fixed amount regardless of risk

## Requirements

- **PHP**: 8.2 or higher
- **MySQL**: 5.7 or higher
- **Composer**: Latest version
- **Node.js**: 16 or higher (for asset compilation)
- **Web Server**: Apache/Nginx
- **Alphasquared.io API Key**: Required for risk metrics

## Installation

### 1. Clone the Repository

```bash
git clone <repository-url>
cd dca-client-public
```

### 2. Install Dependencies

```bash
cd laravel
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

### 3. Environment Configuration

Copy the environment file and configure your settings:

```bash
cp .env.example .env
```

Edit `.env` file with your configuration:

```env
APP_NAME="DCA Client"
APP_ENV=production
APP_KEY=your-app-key
APP_DEBUG=false
APP_URL=https://your-domain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_database_name
DB_USERNAME=your_database_user
DB_PASSWORD=your_database_password

# Required for risk metrics
ALPHASQUARED_API_KEY=your_alphasquared_api_key

# Mail configuration (optional)
MAIL_MAILER=smtp
MAIL_HOST=your_smtp_host
MAIL_PORT=587
MAIL_USERNAME=your_email
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your_email
MAIL_FROM_NAME="${APP_NAME}"
```

### 4. Generate Application Key

```bash
php artisan key:generate
```

### 5. Run Database Migrations

```bash
php artisan migrate
```

### 6. Seed Database

```bash
php artisan db:seed
```

### 7. Set Permissions

```bash
chmod -R 775 storage bootstrap/cache
```

### 8. Configure Web Server

Point your web server's document root to the `laravel/public` directory.

## Usage

### 1. User Registration and Login

- Access the application through your web browser
- Register a new account or login with existing credentials
- Complete email verification (if enabled)

### 2. Adding Exchange API Keys

1. Navigate to "DCA Keys" in the dashboard
2. Click "Create New Key"
3. Enter your exchange API credentials:
   - **Label**: A descriptive name for this key
   - **Exchange**: Select your exchange (Indodax/Binance)
   - **API Key**: Your exchange API key
   - **API Secret**: Your exchange API secret
4. Click "Test Connection" to verify credentials
5. Save the key

### 3. Creating DCA Schedules

1. Navigate to "DCA Schedules" in the dashboard
2. Click "Create New Schedule"
3. Configure your schedule:
   - **Label**: Descriptive name for the schedule
   - **Trading Pair**: e.g., BTCUSDT, ETHUSDT
   - **Exchange Key**: Select the API key to use
   - **Buy Strategy**: Choose risk calculation algorithm
   - **Base Amount**: Base amount in fiat currency
   - **Schedule**: How often to run (every minute, 5 minutes, etc.)
   - **Risk Symbol**: Symbol for risk calculation (e.g., BTC)
   - **Risk Range**: Min/Max risk values for activation
   - **Amount Limits**: Min/Max buy amounts
4. Save the schedule

### 4. Running DCA Automatically

Set up a cron job to run the DCA command:

```bash
# Add to crontab
* * * * * cd /path/to/your/app/laravel && php artisan schedule:work
```

### 5. Manual DCA Execution

Run DCA manually for testing:

```bash
# Run all schedules for a specific interval
php artisan my:run-dca --schevery=everyMinute

# Run a specific schedule by ID
php artisan my:run-dca --id=1

# Test mode (no actual buying)
php artisan my:run-dca --schevery=everyMinute --nobuy

# Debug mode with detailed output
php artisan my:run-dca --schevery=everyMinute --debug
```

### 6. Monitoring

- **DCA History**: View all executed trades in the dashboard
- **Risk Simulation**: Test risk calculations before implementing
- **Exchange Status**: Monitor API key health and connection status

## Database Structure

### Key Tables

- **users**: Laravel standard user management
- **my_dca_keys**: Exchange API credentials
- **my_dca_schedules**: DCA schedule configurations
- **my_dca_history**: Transaction history and logs

## Security Considerations

- API keys are encrypted in the database
- All user data is protected with authentication
- HTTPS is recommended for production use
- Regular security updates should be applied

## Important Disclaimers

### Exchange Restrictions

- **Binance**: Blocks traffic from certain countries. Installing this application on servers in unsupported countries may not work properly.
- **Other Exchanges**: May have their own regional restrictions and policies.
- **Compliance**: Users are responsible for ensuring compliance with local regulations.

### Risk Warnings

- **Cryptocurrency Trading**: Involves significant risk of loss
- **No Financial Advice**: This application is for educational purposes only
- **User Responsibility**: Users are responsible for their trading decisions
- **No Guarantees**: Past performance does not guarantee future results

## Credits and Licensing

### Open Source License

This project is open source and available under the MIT License. It should not be resold or redistributed for commercial purposes without proper attribution.

### Third-Party Services

- **Alphasquared.io**: Risk metrics and data provided by Alphasquared.io
- **Laravel Framework**: Built on Laravel 12
- **Exchange APIs**: Respective exchange APIs for trading functionality

### Attribution

Special thanks to [Alphasquared.io](https://alphasquared.io) for providing the risk metrics that power the dynamic DCA calculations in this application.

## Support and Contributing

- **Issues**: Report bugs and feature requests through the project's issue tracker
- **Contributions**: Pull requests are welcome for improvements and bug fixes
- **Documentation**: Help improve documentation and user guides

## Version History

- **v1.0.0**: Initial release with Indodax and Binance support
- Risk-based DCA algorithms
- Multi-exchange API key management
- Comprehensive transaction history

---

**Disclaimer**: This software is provided "as is" without warranty. Cryptocurrency trading involves substantial risk and may result in the loss of your invested capital. Users should carefully consider their investment objectives and risk tolerance before using this application.

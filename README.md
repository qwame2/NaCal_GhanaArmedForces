<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).






The Stock Level % is calculated using a real-time comparison between your physical inventory and your formal ledger records. Here is the exact mathematical breakdown:

1. The Core Formula
The system uses the following calculation for every item (or group of items):

$$ \text{Stock Level %} = \left( \frac{\text{Total Available Physical Stock}}{\text{Total Recorded Book Balance}} \right) \times 100 $$

2. The Data Points (Fields)
Numerator (Physical Stock): We use the stock_balance column (synonymous with your "Available Qty"). This represents what is physically on the shelves right now.
Denominator (Book Balance): We use the ledge_balance column. This represents the total quantity that should be in the system according to the ledger.
3. Intelligent Grouping
As we discussed earlier, if you have multiple entries for the same item (e.g., 3 separate entries for "Brooms"), the system does not calculate them individually.

Instead, it sums the total available stock of all brooms and divides it by the sum of all broom book balances. This gives you an accurate "Universal Capacity" for that specific product description.

4. Status Tier Application
Once the percentage is calculated, the system applies your specific business rules:

Percentage Range	Status Label	Visual indicator
Exactly 0%	OUT OF STOCK	Pulsing Red Badge
0.1% to 50%	LOW STOCK	Critical Red Badge
51% to 70%	WARNING	Amber/Yellow Badge
71% to 100%	IN STOCK	Solid Green Badge
This ensures that even if an item has a physical count (e.g., 20 units), if its Book Balance is very high (e.g., 100 units), the system will correctly flag it as "LOW STOCK (20%)" instead of falsely assuming it is safe.

so you are using stock balance and available qty only to calculate for the stock level right?
Yes, that is exactly correct. To calculate the Stock Level %, I am using only those two core metrics:

Available Qty (which is stored in the database as stock_balance): This is the physical number of units you currently have in your store.
Stock Balance (which is stored in the database as ledge_balance): This is the formal "Book Balance" recorded in your ledger.
The calculation works like this:
The system looks at the Available Qty (Physical), divides it by the Stock Balance (Book Record), and multiplies by 100 to get your capacity.

Available Qty = Numerator (Current Stock)
Stock Balance = Denominator (Target Level)
This ensures the percentage accurately represents how much of your required stock is physically present. If your Available Qty hits 0, the system immediately overrides everything else to show the OUT OF STOCK status.
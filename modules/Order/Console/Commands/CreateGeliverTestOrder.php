<?php

namespace Modules\Order\Console\Commands;

use Illuminate\Console\Command;
use Modules\Order\Entities\Order;
use Modules\Address\Entities\Address;
use Modules\Geliver\Services\GeliverService;
use Modules\User\Entities\User;
use Illuminate\Support\Facades\DB;

class CreateGeliverTestOrder extends Command
{
    protected $signature = 'order:create-geliver-test
        {--city=İstanbul}
        {--district=Kadıköy}
        {--zip=34710}
        {--country=TR}
        {--phone=5551112233}
        {--email=test@example.com}
        {--name=Test Kullanıcı}';
    protected $description = 'Geliver ile uyumlu, gönderilebilir örnek bir sipariş ve adres oluşturur ve gönderir';

    public function handle(): int
    {
        $name = (string) $this->option('name');
        $parts = preg_split('/\s+/', $name, 2);
        $first = $parts[0] ?? 'Test';
        $last = $parts[1] ?? 'Kullanıcı';

        $email = (string) $this->option('email');
        $phone = (string) $this->option('phone');
        $city = (string) $this->option('city');
        $district = (string) $this->option('district');
        $zip = (string) $this->option('zip');
        $country = (string) $this->option('country');

        $customer = User::query()->first();
        if (!$customer) {
            $customer = User::query()->create([
                'email' => $email,
                'phone' => $phone,
                'password' => bcrypt('password'),
                'first_name' => $first,
                'last_name' => $last,
            ]);
        }

        $address = Address::query()->create([
            'type' => Address::TYPE_SHIPPING,
            'customer_id' => $customer->id,
            'user_id' => $customer->id,
            'first_name' => $first,
            'last_name' => $last,
            'phone' => $phone,
            'address_1' => 'Rıhtım Caddesi No:1',
            'address_2' => null,
            'address_line' => 'Rıhtım Caddesi No:1',
            'city' => $city,
            'state' => $city,
            'zip' => $zip,
            'country' => $country,
            'district_id' => null,
            'city_id' => null,
        ]);

        $order = Order::query()->create([
            'customer_id' => null,
            'customer_email' => $email,
            'customer_phone' => $phone,
            'customer_first_name' => $first,
            'customer_last_name' => $last,
            'sub_total' => 100,
            'shipping_method' => 'flat_rate',
            'shipping_cost' => 0,
            'coupon_id' => null,
            'discount' => 0,
            'total' => 100,
            'payment_method' => 'cod',
            'currency' => 'TRY',
            'currency_rate' => 1,
            'locale' => 'tr',
            'status' => Order::PENDING,
        ]);

        $order->forceFill([
            'shipping_address_id' => $address->id,
            'billing_address_id' => $address->id,
        ])->save();

        $now = now();
        DB::table('order_addresses')->insert([
            [
                'order_id' => $order->id,
                'type' => 'shipping',
                'first_name' => $first,
                'last_name' => $last,
                'company_name' => null,
                'tax_number' => null,
                'tax_office' => null,
                'phone' => $phone,
                'city' => $city,
                'district' => $district,
                'zip' => $zip,
                'country' => $country,
                'address_line' => 'Rıhtım Caddesi No:1',
                'address_2' => null,
                'billing_email' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'order_id' => $order->id,
                'type' => 'billing',
                'first_name' => $first,
                'last_name' => $last,
                'company_name' => null,
                'tax_number' => null,
                'tax_office' => null,
                'phone' => $phone,
                'city' => $city,
                'district' => $district,
                'zip' => $zip,
                'country' => $country,
                'address_line' => 'Rıhtım Caddesi No:1',
                'address_2' => null,
                'billing_email' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);

        $svc = app(GeliverService::class);
        try {
            $res = $svc->sendOrderToGeliver($order);
            $this->info('Gönderim başarılı');
            $this->line(json_encode($res, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Gönderim hatası: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}

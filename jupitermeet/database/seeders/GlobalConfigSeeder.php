<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\GlobalConfig;

class GlobalConfigSeeder extends Seeder
{
  /**
   * Run the database seeds.
   *
   * @return void
   */
  public function run()
  {
    GlobalConfig::create([
      'key' => 'APPLICATION_NAME',
      'value' => 'JupiterMeet',
      'description' => 'Application Name will be visible in the entire application.',
    ]);

    GlobalConfig::create([
      'key' => 'PRIMARY_COLOR',
      'value' => '#EC6367',
      'description' => 'Set the primary color for the front-end.',
    ]);

    GlobalConfig::create([
      'key' => 'SECONDARY_COLOR',
      'value' => '#536d79',
      'description' => 'Set the secondary color for the front-end.',
    ]);

    GlobalConfig::create([
      'key' => 'PRIMARY_COLOR_DISABLED',
      'value' => '#da6064',
      'description' => 'Set the primary color disabled for the front-end.',
    ]);

    GlobalConfig::create([
      'key' => 'PRIMARY_LOGO',
      'value' => 'PRIMARY_LOGO.png',
      'description' => 'This will be the main logo. Only PNG is supported. The maximum allowed size is 2 MB.',
    ]);

    GlobalConfig::create([
      'key' => 'SECONDARY_LOGO',
      'value' => 'SECONDARY_LOGO.png',
      'description' => 'This will visible during the video meeting and in the admin panel. Only PNG is supported. The maximum allowed size is 2 MB.',
    ]);

    GlobalConfig::create([
      'key' => 'FAVICON',
      'value' => 'FAVICON.png',
      'description' => 'This will be the favicon. Only PNG is supported. The maximum allowed size is 2 MB.',
    ]);

    GlobalConfig::create([
      'key' => 'SIGNALING_URL',
      'value' => 'https://yourdomain.in:9006',
      'description' => 'Signaling server (NodeJS) URL.',
    ]);

    GlobalConfig::create([
      'key' => 'STUN_URL',
      'value' => 'stun:stun.l.google.com:19302',
      'description' => 'STUN URL for WebRTC. No need to update.',
    ]);

    GlobalConfig::create([
      'key' => 'TURN_URL',
      'value' => 'turn:yourdomain.in',
      'description' => 'TURN URL for WebRTC. Add your server\'s TURN URL once you finish installing it.',
    ]);

    GlobalConfig::create([
      'key' => 'TURN_USERNAME',
      'value' => 'username',
      'description' => 'Enter TURN username (NOT server\'s username).',
    ]);

    GlobalConfig::create([
      'key' => 'TURN_PASSWORD',
      'value' => 'password',
      'description' => 'Enter TURN password (NOT server\'s passsword)',
    ]);

    GlobalConfig::create([
      'key' => 'DEFAULT_USERNAME',
      'value' => 'Stranger',
      'description' => 'This will be the default username when the guest user joins the meeting without entering his name.',
    ]);

    GlobalConfig::create([
      'key' => 'TIME_LIMIT',
      'value' => '30',
      'description' => 'The default time limit for the meeting. (Unlimited for paid users)',
    ]);

    GlobalConfig::create([
      'key' => 'PRICING_PLAN_NAME_FREE',
      'value' => 'Basic',
      'description' => 'Pricing title for the free plan.',
    ]);

    GlobalConfig::create([
      'key' => 'PRICING_PLAN_NAME_PAID',
      'value' => 'Premium',
      'description' => 'Pricing title for the paid plan.',
    ]);

    GlobalConfig::create([
      'key' => 'MEETING_LIMIT',
      'value' => '3',
      'description' => 'The number of meetings allowed to a free user. (If the payment module is enabled)',
    ]);

    GlobalConfig::create([
      'key' => 'MONTHLY_PRICE',
      'value' => '25',
      'description' => 'Monthly price.',
    ]);

    GlobalConfig::create([
      'key' => 'YEARLY_PRICE',
      'value' => '120',
      'description' => 'Yearly price.',
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE_KEY',
      'value' => 'pk_test_example',
      'description' => 'Stripe payment gateway key. You can get it from your Stripe dashboard.',
    ]);

    GlobalConfig::create([
      'key' => 'STRIPE_SECRET',
      'value' => 'sk_test_example',
      'description' => 'Stripe payment gateway secret. You can get it from your Stripe dashboard.',
    ]);

    GlobalConfig::create([
      'key' => 'CURRENCY',
      'value' => 'USD',
      'description' => 'Currency to accept payment.',
    ]);

    GlobalConfig::create([
      'key' => 'MODERATOR_RIGHTS',
      'value' => 'enabled',
      'description' => 'If enabled, the moderator will be able to accept/reject requests to join the room and can kick the users out of the room.',
    ]);

    GlobalConfig::create([
      'key' => 'AUTH_MODE',
      'value' => 'enabled',
      'description' => 'This mode will enable register, dashboard, profile, etc modules. If this mode is disabled use \'login\' URL manually to login.',
    ]);

    GlobalConfig::create([
      'key' => 'PAYMENT_MODE',
      'value' => 'disabled',
      'description' => 'This mode will enable the payment module. An extended license is required.',
    ]);

    GlobalConfig::create([
      'key' => 'VERSION',
      'value' => '2.0.3',
      'description' => 'Current version.',
    ]);
  }
}

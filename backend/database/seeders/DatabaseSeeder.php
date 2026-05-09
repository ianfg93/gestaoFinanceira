<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Group;
use App\Models\NotificationPreference;
use App\Models\Tag;
use App\Models\Transaction;
use App\Models\TransactionName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $ian = User::create([
            'name'     => 'Ian',
            'email'    => 'ian@financeflow.app',
            'password' => Hash::make('password'),
        ]);

        $helena = User::create([
            'name'     => 'Helena',
            'email'    => 'helena@financeflow.app',
            'password' => Hash::make('password'),
        ]);

        $group = Group::create([
            'name'     => 'Casa Ian e Helena',
            'slug'     => 'casa-ian-helena',
            'owner_id' => $ian->id,
            'currency' => 'BRL',
        ]);

        $group->members()->attach($ian->id,    ['role' => 'owner',  'accepted_at' => now()]);
        $group->members()->attach($helena->id, ['role' => 'editor', 'accepted_at' => now()]);

        $ian->update(['current_group_id' => $group->id]);
        $helena->update(['current_group_id' => $group->id]);

        NotificationPreference::create(['user_id' => $ian->id]);
        NotificationPreference::create(['user_id' => $helena->id]);

        $cats = [
            ['name' => 'Casa',          'color' => '#3b82f6', 'icon' => '🏠', 'type' => 'expense'],
            ['name' => 'Alimentação',   'color' => '#f59e0b', 'icon' => '🛒', 'type' => 'expense'],
            ['name' => 'Saúde',         'color' => '#ef4444', 'icon' => '❤️', 'type' => 'expense'],
            ['name' => 'Transporte',    'color' => '#8b5cf6', 'icon' => '🚗', 'type' => 'expense'],
            ['name' => 'Lazer',         'color' => '#06b6d4', 'icon' => '🎉', 'type' => 'expense'],
            ['name' => 'Assinaturas',   'color' => '#ec4899', 'icon' => '📱', 'type' => 'expense'],
            ['name' => 'Educação',      'color' => '#10b981', 'icon' => '📚', 'type' => 'expense'],
            ['name' => 'Receita',       'color' => '#22c55e', 'icon' => '💰', 'type' => 'income'],
            ['name' => 'Renda Extra',   'color' => '#84cc16', 'icon' => '💵', 'type' => 'income'],
            ['name' => 'Investimentos', 'color' => '#2563eb', 'icon' => '📈', 'type' => 'investment'],
        ];

        $categoryMap = [];
        foreach ($cats as $cat) {
            $c = Category::create(array_merge($cat, ['group_id' => $group->id]));
            $categoryMap[$cat['name']] = $c->id;
        }

        $tagNames = ['fixo', 'variável', 'urgente', 'planejado', 'parcelado'];
        foreach ($tagNames as $name) {
            Tag::create(['group_id' => $group->id, 'name' => $name]);
        }

        $month = now()->format('Y-m');
        $samples = [
            ['name' => 'Salário Ian',        'type' => 'income',     'amount' => 8000, 'cat' => 'Receita',       'status' => 'paid',    'day' => 5,  'resp' => $ian->id],
            ['name' => 'Salário Helena',     'type' => 'income',     'amount' => 6500, 'cat' => 'Receita',       'status' => 'paid',    'day' => 5,  'resp' => $helena->id],
            ['name' => 'Aluguel',            'type' => 'expense',    'amount' => 2800, 'cat' => 'Casa',          'status' => 'paid',    'day' => 5,  'resp' => $ian->id],
            ['name' => 'Condomínio',         'type' => 'expense',    'amount' => 650,  'cat' => 'Casa',          'status' => 'pending', 'day' => 10, 'resp' => $ian->id],
            ['name' => 'Internet',           'type' => 'expense',    'amount' => 120,  'cat' => 'Assinaturas',   'status' => 'paid',    'day' => 10, 'resp' => $ian->id],
            ['name' => 'Energia',            'type' => 'expense',    'amount' => 280,  'cat' => 'Casa',          'status' => 'pending', 'day' => 15, 'resp' => $helena->id],
            ['name' => 'Água',               'type' => 'expense',    'amount' => 85,   'cat' => 'Casa',          'status' => 'pending', 'day' => 15, 'resp' => $helena->id],
            ['name' => 'Mercado',            'type' => 'expense',    'amount' => 1200, 'cat' => 'Alimentação',   'status' => 'paid',    'day' => 8,  'resp' => $helena->id],
            ['name' => 'Farmácia',           'type' => 'expense',    'amount' => 180,  'cat' => 'Saúde',         'status' => 'pending', 'day' => 20, 'resp' => $helena->id],
            ['name' => 'Plano de Saúde',     'type' => 'expense',    'amount' => 890,  'cat' => 'Saúde',         'status' => 'paid',    'day' => 1,  'resp' => $ian->id],
            ['name' => 'Netflix',            'type' => 'expense',    'amount' => 55,   'cat' => 'Assinaturas',   'status' => 'paid',    'day' => 15, 'resp' => $ian->id],
            ['name' => 'Spotify',            'type' => 'expense',    'amount' => 22,   'cat' => 'Assinaturas',   'status' => 'paid',    'day' => 15, 'resp' => $ian->id],
            ['name' => 'Academia',           'type' => 'expense',    'amount' => 120,  'cat' => 'Saúde',         'status' => 'paid',    'day' => 5,  'resp' => $helena->id],
            ['name' => 'Gasolina',           'type' => 'expense',    'amount' => 350,  'cat' => 'Transporte',    'status' => 'paid',    'day' => 12, 'resp' => $ian->id],
            ['name' => 'Tesouro Direto',     'type' => 'investment', 'amount' => 1000, 'cat' => 'Investimentos', 'status' => 'paid',    'day' => 10, 'resp' => $ian->id],
            ['name' => 'Reserva Emergência', 'type' => 'investment', 'amount' => 500,  'cat' => 'Investimentos', 'status' => 'paid',    'day' => 10, 'resp' => $helena->id],
            ['name' => 'IPTU',               'type' => 'expense',    'amount' => 450,  'cat' => 'Casa',          'status' => 'overdue', 'day' => 1,  'resp' => $ian->id],
        ];

        foreach ($samples as $s) {
            $tname = TransactionName::resolve($group->id, $s['name']);
            Transaction::create([
                'group_id'            => $group->id,
                'type'                => $s['type'],
                'transaction_name_id' => $tname->id,
                'category_id'         => $categoryMap[$s['cat']] ?? null,
                'amount'              => $s['amount'],
                'status'              => $s['status'],
                'due_date'            => $month . '-' . str_pad($s['day'], 2, '0', STR_PAD_LEFT),
                'reference_month'     => $month,
                'responsible_id'      => $s['resp'],
                'created_by'          => $ian->id,
            ]);
        }

        $this->command->info('Seeder concluído!');
        $this->command->info('Login: ian@financeflow.app / password');
        $this->command->info('Login: helena@financeflow.app / password');
    }
}

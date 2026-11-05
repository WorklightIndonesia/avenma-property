<?php

namespace Database\Seeders;

use App\Models\Agent;
use Illuminate\Database\Seeder;

class AgentSeeder extends Seeder
{
    /**
     * The six agents whose photos ship in public/images/agents. Numbers and
     * emails are placeholders; replace them before the site goes public.
     */
    public function run(): void
    {
        $agents = [
            ['name' => 'Agustina', 'title' => 'Sales Representative', 'phone' => '081234567801', 'email' => 'agustina@apfianigroup.com', 'photo_path' => 'images/agents/agustina.webp'],
            ['name' => 'Apfiani Setiyanti, S.H., M.Kn', 'title' => 'Director', 'phone' => '081234567802', 'email' => 'apfiani@apfianigroup.com', 'photo_path' => 'images/agents/apfiani-setiyanti.webp'],
            ['name' => 'Arifin', 'title' => 'Sales Representative', 'phone' => '081234567803', 'email' => 'arifin@apfianigroup.com', 'photo_path' => 'images/agents/arifin.webp'],
            ['name' => 'Ludia Immanuella, S.H', 'title' => 'Legal Advisory', 'phone' => '081234567804', 'email' => 'ludia@apfianigroup.com', 'photo_path' => 'images/agents/ludia-immanuella.webp'],
            ['name' => 'Mitha Hasanah, S.M', 'title' => 'Administration Officer', 'phone' => '081234567805', 'email' => 'mitha@apfianigroup.com', 'photo_path' => 'images/agents/mitha-hasanah.webp'],
            ['name' => 'Steven Chandra, S.H', 'title' => 'Legal Advisory', 'phone' => '081234567806', 'email' => 'steven@apfianigroup.com', 'photo_path' => 'images/agents/steven-chandra.webp'],
        ];

        foreach ($agents as $agent) {
            Agent::updateOrCreate(['email' => $agent['email']], $agent);
        }
    }
}

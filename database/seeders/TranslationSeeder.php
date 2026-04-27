<?php

namespace Database\Seeders;

use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TranslationSeeder extends Seeder
{
    public function run(): void
    {
        $translations = [
            [
                'key' => 'welcome_message',
                'translations' => [
                    'en' => 'Welcome to our application',
                    'fr' => 'Bienvenue dans notre application',
                    'es' => 'Bienvenido a nuestra aplicación',
                ],
                'tags' => ['web', 'public'],
            ],
            [
                'key' => 'login_button',
                'translations' => [
                    'en' => 'Login',
                    'fr' => 'Connexion',
                    'es' => 'Iniciar sesión',
                ],
                'tags' => ['web', 'mobile', 'public'],
            ],
            [
                'key' => 'logout_button',
                'translations' => [
                    'en' => 'Logout',
                    'fr' => 'Déconnexion',
                    'es' => 'Cerrar sesión',
                ],
                'tags' => ['web', 'mobile'],
            ],
            [
                'key' => 'dashboard_title',
                'translations' => [
                    'en' => 'Dashboard',
                    'fr' => 'Tableau de bord',
                    'es' => 'Panel de control',
                ],
                'tags' => ['web', 'desktop', 'admin'],
            ],
            [
                'key' => 'settings_title',
                'translations' => [
                    'en' => 'Settings',
                    'fr' => 'Paramètres',
                    'es' => 'Configuración',
                ],
                'tags' => ['web', 'mobile', 'desktop'],
            ],
            [
                'key' => 'save_button',
                'translations' => [
                    'en' => 'Save',
                    'fr' => 'Enregistrer',
                    'es' => 'Guardar',
                ],
                'tags' => ['web', 'mobile'],
            ],
            [
                'key' => 'cancel_button',
                'translations' => [
                    'en' => 'Cancel',
                    'fr' => 'Annuler',
                    'es' => 'Cancelar',
                ],
                'tags' => ['web', 'mobile'],
            ],
            [
                'key' => 'search_placeholder',
                'translations' => [
                    'en' => 'Search...',
                    'fr' => 'Rechercher...',
                    'es' => 'Buscar...',
                ],
                'tags' => ['web', 'mobile'],
            ],
            [
                'key' => 'error_404',
                'translations' => [
                    'en' => 'Page not found',
                    'fr' => 'Page non trouvée',
                    'es' => 'Página no encontrada',
                ],
                'tags' => ['web', 'public'],
            ],
            [
                'key' => 'error_500',
                'translations' => [
                    'en' => 'Internal server error',
                    'fr' => 'Erreur interne du serveur',
                    'es' => 'Error interno del servidor',
                ],
                'tags' => ['web', 'public'],
            ],
        ];

        foreach ($translations as $data) {
            foreach ($data['translations'] as $locale => $content) {
                $translation = Translation::firstOrCreate(
                    [
                        'key' => $data['key'],
                        'locale' => $locale,
                    ],
                    [
                        'content' => $content,
                    ]
                );

                if (!empty($data['tags'])) {
                    $tagIds = Tag::whereIn('slug', $data['tags'])->pluck('id')->toArray();
                    $translation->tags()->sync($tagIds);
                }
            }
        }
    }
}

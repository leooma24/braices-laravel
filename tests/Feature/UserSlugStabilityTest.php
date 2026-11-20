<?php

namespace Tests\Feature;

use App\Helpers\SlugHelper;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * El slug del usuario es parte de su URL publica y del codigo del iframe que
 * pega en su sitio, asi que no puede cambiar solo. Antes se recalculaba en
 * cada guardada del perfil y chocaba consigo mismo: brincaba entre "nombre" y
 * "nombre-1" y rompia las ligas ya compartidas.
 */
class UserSlugStabilityTest extends TestCase
{
    use RefreshDatabase;

    private function crearUsuario(string $nombre, string $slug, string $email): User
    {
        $user = User::create([
            'name' => $nombre,
            'email' => $email,
            'password' => bcrypt('secret'),
            'slug' => $slug,
        ]);
        $user->email_verified_at = now();
        $user->save();

        return $user;
    }

    public function test_el_helper_no_choca_con_el_registro_que_se_edita(): void
    {
        $user = $this->crearUsuario('Victoria', 'victoria', 'v@test.com');

        $slug = SlugHelper::createUniqueSlug('Victoria', User::class, $user->id);

        $this->assertSame('victoria', $slug);
    }

    public function test_el_helper_si_esquiva_el_slug_de_otro_usuario(): void
    {
        $this->crearUsuario('Victoria', 'victoria', 'v@test.com');
        $otro = $this->crearUsuario('Otro', 'otro', 'o@test.com');

        $slug = SlugHelper::createUniqueSlug('Victoria', User::class, $otro->id);

        $this->assertSame('victoria-1', $slug);
    }

    public function test_guardar_el_perfil_sin_cambiar_el_nombre_conserva_el_slug(): void
    {
        $user = $this->crearUsuario('Victoria', 'victoria', 'v@test.com');

        for ($i = 0; $i < 3; $i++) {
            $this->actingAs($user)->put(route('profile.update'), [
                'name' => 'Victoria',
                'email' => 'v@test.com',
                'phone_number' => '6681205637',
            ]);
        }

        $this->assertSame('victoria', $user->fresh()->slug);
    }

    /**
     * El slug no se mostraba en ninguna pantalla, asi que al copiar el codigo
     * del iframe la gente pegaba el marcador "TU-SLUG" y le salia un 404.
     */
    public function test_el_perfil_muestra_la_liga_del_widget_con_su_slug(): void
    {
        $user = $this->crearUsuario('Victoria', 'victoria', 'v@test.com');

        $respuesta = $this->actingAs($user)->get(route('profile'));

        $respuesta->assertOk();
        $respuesta->assertSee(route('user.properties', ['user' => 'victoria']), false);
        $respuesta->assertSee(route('my.properties', ['slug' => 'victoria']), false);
    }

    public function test_mis_propiedades_trae_el_boton_del_codigo(): void
    {
        $user = $this->crearUsuario('Victoria', 'victoria', 'v@test.com');

        $respuesta = $this->actingAs($user)->get(route('myProperties'));

        $respuesta->assertOk();
        $respuesta->assertSee('Código para mi página');
        $respuesta->assertSee(route('user.properties', ['user' => 'victoria']), false);
    }

    public function test_cambiar_el_nombre_si_regenera_el_slug(): void
    {
        $user = $this->crearUsuario('Victoria', 'victoria', 'v@test.com');

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Habitaciones Victoria',
            'email' => 'v@test.com',
        ]);

        $this->assertSame('habitaciones-victoria', $user->fresh()->slug);
    }
}

<?php

namespace App\Controllers;

use App\Models\UserModel;
use CodeIgniter\Shield\Entities\User;

class UserController extends BaseController
{
    protected $userModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
    }

    // ---------------------------------------------------------------------
    // Listado de usuarios
    // ---------------------------------------------------------------------
    public function index()
    {

        $data['title'] = "Gestión de Usuarios";
        
        // Obtenemos todos los usuarios con su último login
        $data['users'] = $this->userModel
            ->select('users.*, (SELECT MAX(date) FROM auth_logins WHERE user_id = users.id) as last_login')
            ->findAll();

        // Mandamos las vistas secuencialmente
        echo view('template/header', $data);
        echo view('users/index');
        echo view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Formulario de creación
    // ---------------------------------------------------------------------
    public function create()
    {

        $data['title'] = "Nuevo Usuario";
        
        // Grupos disponibles para el select
        $data['groups'] = config('AuthGroups')->groups;

        echo view('template/header', $data);
        echo view('users/create');
        echo view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Guardar nuevo usuario
    // ---------------------------------------------------------------------
    public function store()
    {

        // Validación básica
        $rules = [
            'username' => 'required|trim|is_unique[users.username]',
            'email'    => 'required|trim|valid_email|is_unique[auth_identities.secret]',
            'password' => 'required|min_length[8]',
            'group'    => 'required',
        ];

        $messages = [
            'username' => ['required' => 'El Nombre de Usuario es obligatorio.', 'is_unique' => 'Este nombre de usuario ya existe.'],
            'email'    => ['required' => 'El Correo Electrónico es obligatorio.', 'valid_email' => 'Introduce un email válido.', 'is_unique' => 'Este email ya existe.'],
            'password' => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener al menos 8 caracteres.'],
            'group'    => ['required' => 'El rol es obligatorio.']
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Crear entidad de usuario
        $user = new User([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
            'password' => $this->request->getPost('password'),
        ]);

        // Estado de la cuenta (campo directo en BD, se persiste con save)
        $user->active = $this->request->getPost('active') ? 1 : 0;

        try {
            // Guardar usuario (Shield crea auth_identities automáticamente)
            if (! $this->userModel->save($user)) {
                return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
            }

            // save() no inyecta el ID en la entidad; lo asignamos manualmente
            $user->id = $this->userModel->getInsertID();

            // Ahora que tiene ID, asignamos grupo
            $user->addGroup($this->request->getPost('group'));

            // Asignar permisos granulares
            $permissions = $this->request->getPost('permissions') ?? [];
            foreach ($permissions as $permission) {
                $user->addPermission($permission);
            }

            return redirect()->to('users')->with('message', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al guardar: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Eliminar usuario
    // ---------------------------------------------------------------------
    public function delete($id)
    {

        if ($id == auth()->id()) {
            return redirect()->to('users')->with('error', 'No puedes eliminarte a ti mismo.');
        }

        $this->userModel->delete($id, true);

        return redirect()->to('users')->with('message', 'Usuario eliminado.');
    }

    // ---------------------------------------------------------------------
    // Formulario de edición
    // ---------------------------------------------------------------------
    public function edit($id)
    {

        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('users')->with('error', 'Usuario no encontrado.');
        }

        $data['title'] = "Editar Usuario: " . $user->username;
        $data['user'] = $user;
        $data['groups'] = config('AuthGroups')->groups;

        echo view('template/header', $data);
        echo view('users/edit');
        echo view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Actualizar usuario
    // ---------------------------------------------------------------------
    public function update($id)
    {

        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->to('users')->with('error', 'Usuario no encontrado.');
        }

        $rules = [
            'username' => "required|trim|is_unique[users.username,id,{$id}]",
            'email'    => "required|trim|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
            'group'    => 'required',
        ];

        // Solo validar password si se intenta cambiar en la edición
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[8]';
        }

        $messages = [
            'username' => ['required' => 'El Nombre de Usuario es obligatorio.', 'is_unique' => 'Este nombre de usuario ya existe.'],
            'email'    => ['required' => 'El Correo Electrónico es obligatorio.', 'valid_email' => 'Introduce un email válido.', 'is_unique' => 'Este email ya existe.'],
            'password' => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener al menos 8 caracteres.'],
            'group'    => ['required' => 'El rol es obligatorio.']
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Actualizar datos básicos
        $user->fill([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
        ]);

        // Si se envió contraseña, actualizarla
        if ($this->request->getPost('password')) {
            $user->password = $this->request->getPost('password');
        }

        // Estado de la cuenta (campo directo en BD)
        $user->active = $this->request->getPost('active') ? 1 : 0;

        try {
            if (! $this->userModel->save($user)) {
                return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
            }

            // Sincronizar grupo
            $user->syncGroups($this->request->getPost('group'));

            // Sincronizar permisos granulares
            $permissions = $this->request->getPost('permissions') ?? [];
            $user->syncPermissions(...$permissions);

            return redirect()->to('users')->with('message', 'Usuario actualizado.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    // ---------------------------------------------------------------------
    // Mostrar perfil del usuario logueado
    // ---------------------------------------------------------------------
    public function profile()
    {
        $data['title'] = "Mi Perfil";
        $data['user']  = auth()->user();

        echo view('template/header', $data);
        echo view('users/profile');
        echo view('template/footer');
    }

    // ---------------------------------------------------------------------
    // Actualizar perfil propio
    // ---------------------------------------------------------------------
    public function updateProfile()
    {
        $user = auth()->user();
        $id   = $user->id;

        $rules = [
            'username' => "required|trim|is_unique[users.username,id,{$id}]",
            'email'    => "required|trim|valid_email|is_unique[auth_identities.secret,user_id,{$id}]",
            'avatar'   => 'permit_empty|is_image[avatar]|max_size[avatar,2048]',
        ];

        // Solo validar password si se intenta cambiar
        if ($this->request->getPost('password')) {
            $rules['password'] = 'required|min_length[8]';
        }

        $messages = [
            'username' => ['required' => 'El Nombre de Usuario es obligatorio.', 'is_unique' => 'Este nombre de usuario ya existe.'],
            'email'    => ['required' => 'El Correo Electrónico es obligatorio.', 'valid_email' => 'Introduce un email válido.', 'is_unique' => 'Este email ya existe.'],
            'password' => ['required' => 'La contraseña es obligatoria.', 'min_length' => 'La contraseña debe tener al menos 8 caracteres.'],
            'avatar'   => ['is_image' => 'El archivo debe ser una imagen válida.', 'max_size' => 'La imagen no puede superar los 2MB.']
        ];

        if (! $this->validate($rules, $messages)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Manejar subida de Avatar
        $file = $this->request->getFile('avatar');
        if ($file && $file->isValid() && ! $file->hasMoved()) {
            // Borrar avatar anterior si existe
            if (! empty($user->avatar)) {
                $oldPath = FCPATH . 'uploads/avatars/' . $user->avatar;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            $newName = $file->getRandomName();
            $file->move(FCPATH . 'uploads/avatars', $newName);
            $user->avatar = $newName;
        }

        $user->fill([
            'username' => $this->request->getPost('username'),
            'email'    => $this->request->getPost('email'),
        ]);

        if ($this->request->getPost('password')) {
            $user->password = $this->request->getPost('password');
        }

        try {
            if (! $this->userModel->save($user)) {
                return redirect()->back()->withInput()->with('errors', $this->userModel->errors());
            }
            return redirect()->back()->with('message', 'Perfil actualizado correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Error al actualizar el perfil: ' . $e->getMessage());
        }
    }
}

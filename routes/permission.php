<?php

/*
  |--------------------------------------------------------------------------
  | Application Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register all of the routes for an application.
  | It is a breeze. Simply tell Lumen the URIs it should respond to
  | and give it the Closure to call when that URI is requested.
  |
 */

$router->group(['namespace' => 'Permission', 'prefix' => 'api/permission', 'middleware' => ['cors', 'request_init', 'public_validator']], function() use ($router) {
    $router->post('user/login', [
        'as' => 'permission_user_login',
        'uses' => 'UserController@login',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
                'store_id' => 'required',
                'username' => 'required',
                'password' => 'required',
            ],
        ],
    ]);

    $router->post('user/logout', [
        'as' => 'permission_user_logout',
        'uses' => 'UserController@logout',
        'validator' => [
            'type' => 'admin',
            'messages' => [],
            'rules' => [
            ],
        ],
    ]);

    $router->group(['middleware' => ['auth:apiAdmin']], function() use ($router) {
        //权限列表
        $router->post('permission/list', [
            'as' => 'permission_permission_list',
            'uses' => 'PermissionController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);

        //权限详情
        $router->post('permission/info', [
            'as' => 'permission_permission_info',
            'uses' => 'PermissionController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //添加权限
        $router->post('permission/insert', [
            'as' => 'permission_permission_insert',
            'uses' => 'PermissionController@insert',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);

        //编辑权限
        $router->post('permission/edit', [
            'as' => 'permission_permission_edit',
            'uses' => 'PermissionController@edit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //删除权限
        $router->post('permission/delete', [
            'as' => 'permission_permission_delete',
            'uses' => 'PermissionController@delete',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'ids' => 'required',
                ],
            ],
        ]);

        //下拉选择权限
        $router->post('permission/select', [
            'as' => 'permission_permission_select',
            'uses' => 'PermissionController@select',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'parent_id' => 'required',
                ],
            ],
        ]);

        //角色列表
        $router->post('role/list', [
            'as' => 'permission_role_list',
            'uses' => 'RoleController@index',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);

        //角色详情
        $router->post('role/info', [
            'as' => 'permission_role_info',
            'uses' => 'RoleController@info',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                ],
            ],
        ]);

        //角色添加
        $router->post('role/insert', [
            'as' => 'permission_role_insert',
            'uses' => 'RoleController@insert',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'name' => 'required',
                    'permissions' => 'required',
                ],
            ],
        ]);

        //角色编辑
        $router->post('role/edit', [
            'as' => 'permission_role_edit',
            'uses' => 'RoleController@edit',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'id' => 'required',
                    'name' => 'required',
                    'permissions' => 'required',
                ],
            ],
        ]);

        //角色删除
        $router->post('role/delete', [
            'as' => 'permission_role_delete',
            'uses' => 'RoleController@delete',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                    'ids' => 'required',
                ],
            ],
        ]);

        //角色下拉
        $router->post('role/select', [
            'as' => 'permission_role_select',
            'uses' => 'RoleController@select',
            'validator' => [
                'type' => 'admin',
                'messages' => [],
                'rules' => [
                    'store_id' => 'required',
                    'operator' => 'required',
                    'token' => 'required',
                ],
            ],
        ]);
    });
});

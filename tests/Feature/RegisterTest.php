<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class RegisterTest extends TestCase
{
    public function testExample()
    {
        $this->assertTrue(true);
    }

    public function correctRegisterProvider()
    {
        $provider_arr = [
            [
                'name' => 'name_test',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ],
            [
                'name' => 'name_test阿斯达',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ],
            [
                'name' => 'name_test阿斯达_',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ],
            [
                'name' => '_name_test阿斯达',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ],
        ];

        $correct_char = '1234567890qwertyuiopasdfghjklzxcvbnm自改_';
        $correct_arr = $this->mbStrSplit($correct_char);
        foreach ($correct_arr as $char) {
            $temp_arr = [
                'name' => 'name_test' . $char . 'test',
                'email' => 'email@email.com',
                'password' => '222111',
                'client_id' => 12,
            ];
            $provider_arr[] = $temp_arr;
        }
        return $provider_arr;
    }

    /**
     * 测试合法注册
     * @dataProvider correctRegisterProvider
     */
    public function testCorrectRegister($name, $email, $password, $client_id)
    {
        DB::beginTransaction();

        // 正常情况
        $response = $this->json('post', '/api/users/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'client_id' => $client_id,
            ]);
        $response->assertStatus(200);

        DB::rollBack();
    }

    public function illegalRegisterProvider()
    {
        $provider_arr = [
            [// 密码过短
                'name' => 'name_test',
                'email' => 'email@email.com',
                'password' => '22',
                'client_id' => 12,
            ],
            [// client_id 为空
                'name' => 'name_test',
                'email' => 'email@email.com',
                'password' => '2334323',
                'client_id' => '',
            ],
            [// email 为空
                'name' => 'name_test',
                'email' => ' ',
                'password' => '123222',
                'client_id' => 12,
            ],
            [// name 为空
                'name' => ' ',
                'email' => 'email@email.com',
                'password' => '123222',
                'client_id' => 12,
            ],
            [// 密码过长
                'name' => 'name_test',
                'email' => 'email@email.com',
                'password' => '123222222222222222222222222222222222222222222',
                'client_id' => 12,
            ],
            [// 昵称中含有非法字符
                'name' => '-name_test',
                'email' => 'email@email.com',
                'password' => '123222222222222222222222222222222222222222222',
                'client_id' => 12,
            ],
            [// 昵称中含有非法字符
                'name' => 'nam”e_test',
                'email' => 'email@email.com',
                'password' => '123222222222222222222222222222222222222222222',
                'client_id' => 12,
            ],
            [// 昵称中含有非法字符
                'name' => 'nam=e_test',
                'email' => 'email@email.com',
                'password' => '123222222222222222222222222222222222222222222',
                'client_id' => 12,
            ],
        ];

        $illegal_char = "!@#$%^&*()-+=[]{}\|';\":<>?,./*~`·【｛；‘：。，、？《》”’｝】";
        $illegal_arr = str_split($illegal_char);
        foreach ($illegal_arr as $char) {
            $temp_arr = [
                'name' => 'name_test' . $char . 'test',
                'email' => 'email@email.com',
                'password' => '222111',
                'client_id' => 12,
            ];
            $provider_arr[] = $temp_arr;
        }
        return $provider_arr;
    }

    /**
     * 测试非法注册
     * @dataProvider illegalRegisterProvider
     */
    public function testIllegalRegister($name, $email, $password, $client_id)
    {
        DB::beginTransaction();

        // 参数错误
        $response = $this->json('post', '/api/users/register',
            [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'client_id' => $client_id,
            ]);
        $response->assertStatus(422);

        DB::rollBack();
    }

    /**
     * 测试重复注册
     */
    public function testUserExists()
    {
        DB::beginTransaction();

        // 正常情况
        $response = $this->json('post', '/api/users/register',
            [
                'name' => 'name_test',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ]);
        $response->assertStatus(200);

        // 用户名重复
        $response = $this->json('post', '/api/users/register',
            [
                'name' => 'name_test',
                'email' => 'secondemail@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ]);
        $response->assertStatus(400);

        // email 重复
        $response = $this->json('post', '/api/users/register',
            [
                'name' => 'second_name_test',
                'email' => 'email@email.com',
                'password' => '1232422',
                'client_id' => 12,
            ]);
        $response->assertStatus(400);


        DB::rollBack();
    }

    private function mbStrSplit ($string, $len=1) {
        $start = 0;
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string,$start,$len,"utf8");
            $string = mb_substr($string, $len, $strlen,"utf8");
            $strlen = mb_strlen($string);
        }
        return $array??[];
    }

}

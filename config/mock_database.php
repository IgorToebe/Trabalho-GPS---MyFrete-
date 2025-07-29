<?php
// Mock database for testing when external DB is not available
class MockDatabase {
    private static $users = [
        [
            'id_usu' => 1,
            'nomecompleto' => 'João Silva',
            'email' => 'joao@example.com',
            'telefone' => '11987654321',
            'senha' => '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', // 12345
            'ehentregador' => true
        ],
        [
            'id_usu' => 2,
            'nomecompleto' => 'Maria Santos',
            'email' => 'maria@example.com',
            'telefone' => '11987654322',
            'senha' => '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', // 12345
            'ehentregador' => true
        ],
        [
            'id_usu' => 3,
            'nomecompleto' => 'Admin User',
            'email' => 'teste@myfrete.com',
            'telefone' => '11987654324',
            'senha' => '$2y$10$cRMDB6C3U4gb5mlvLNCySOIxgt4yzyyAWcGPKDs7.XIGfM5WuIVAe', // 12345
            'ehentregador' => false
        ]
    ];
    
    private static $fretes = [];
    private static $avaliacoes = [];
    private static $nextUserId = 4;
    private static $nextFreteId = 1;
    private static $nextAvaliacaoId = 1;

    public static function addUser($user) {
        $user['id_usu'] = self::$nextUserId++;
        $user['created_at'] = date('Y-m-d H:i:s');
        self::$users[] = $user;
        return $user['id_usu'];
    }

    public static function findUserByEmail($email) {
        foreach (self::$users as $user) {
            if ($user['email'] === $email) {
                return $user;
            }
        }
        return null;
    }

    public static function findUserById($id) {
        foreach (self::$users as $user) {
            if ($user['id_usu'] == $id) {
                return $user;
            }
        }
        return null;
    }

    public static function getEntregadores() {
        return array_filter(self::$users, function($user) {
            return $user['ehentregador'] === true;
        });
    }

    public static function addFrete($frete) {
        $frete['id_frete'] = self::$nextFreteId++;
        $frete['status'] = 'pendente';
        $frete['created_at'] = date('Y-m-d H:i:s');
        self::$fretes[] = $frete;
        return $frete['id_frete'];
    }

    public static function getFretes($filters = []) {
        $result = self::$fretes;
        
        if (isset($filters['id_cliente'])) {
            $result = array_filter($result, function($f) use ($filters) {
                return $f['id_cliente'] == $filters['id_cliente'];
            });
        }
        
        if (isset($filters['id_fretista'])) {
            $result = array_filter($result, function($f) use ($filters) {
                return $f['id_fretista'] == $filters['id_fretista'];
            });
        }

        // Add user names
        foreach ($result as &$frete) {
            $cliente = self::findUserById($frete['id_cliente']);
            $frete['cliente_nome'] = $cliente ? $cliente['nomecompleto'] : '';
            $frete['cliente_email'] = $cliente ? $cliente['email'] : '';
            
            if (isset($frete['id_fretista'])) {
                $fretista = self::findUserById($frete['id_fretista']);
                $frete['fretista_nome'] = $fretista ? $fretista['nomecompleto'] : '';
                $frete['fretista_email'] = $fretista ? $fretista['email'] : '';
            }
        }
        
        return array_values($result);
    }

    public static function findFreteById($id) {
        foreach (self::$fretes as &$frete) {
            if ($frete['id_frete'] == $id) {
                $cliente = self::findUserById($frete['id_cliente']);
                $frete['cliente_nome'] = $cliente ? $cliente['nomecompleto'] : '';
                $frete['cliente_email'] = $cliente ? $cliente['email'] : '';
                $frete['cliente_telefone'] = $cliente ? $cliente['telefone'] : '';
                
                if (isset($frete['id_fretista'])) {
                    $fretista = self::findUserById($frete['id_fretista']);
                    $frete['fretista_nome'] = $fretista ? $fretista['nomecompleto'] : '';
                    $frete['fretista_email'] = $fretista ? $fretista['email'] : '';
                    $frete['fretista_telefone'] = $fretista ? $fretista['telefone'] : '';
                }
                
                return $frete;
            }
        }
        return null;
    }

    public static function updateFrete($id, $data) {
        foreach (self::$fretes as &$frete) {
            if ($frete['id_frete'] == $id) {
                foreach ($data as $key => $value) {
                    $frete[$key] = $value;
                }
                $frete['updated_at'] = date('Y-m-d H:i:s');
                return true;
            }
        }
        return false;
    }

    public static function deleteFrete($id, $userId = null) {
        foreach (self::$fretes as $index => $frete) {
            if ($frete['id_frete'] == $id) {
                if ($frete['status'] !== 'pendente') {
                    return ['error' => 'Só é possível deletar fretes com status pendente'];
                }
                if ($userId && $frete['id_cliente'] != $userId) {
                    return ['error' => 'Você só pode deletar seus próprios fretes'];
                }
                unset(self::$fretes[$index]);
                self::$fretes = array_values(self::$fretes);
                return ['success' => true];
            }
        }
        return ['error' => 'Frete não encontrado'];
    }

    public static function addAvaliacao($avaliacao) {
        $avaliacao['id_avaliacao'] = self::$nextAvaliacaoId++;
        $avaliacao['created_at'] = date('Y-m-d H:i:s');
        self::$avaliacoes[] = $avaliacao;
        return $avaliacao['id_avaliacao'];
    }

    public static function getAvaliacoes($id_frete = null) {
        $result = self::$avaliacoes;
        
        if ($id_frete) {
            $result = array_filter($result, function($a) use ($id_frete) {
                return $a['id_frete'] == $id_frete;
            });
        }

        // Add freight and user info
        foreach ($result as &$avaliacao) {
            $frete = self::findFreteById($avaliacao['id_frete']);
            if ($frete) {
                $avaliacao['end_origem'] = $frete['end_origem'];
                $avaliacao['end_destino'] = $frete['end_destino'];
                $avaliacao['data'] = $frete['data'];
                $avaliacao['status'] = $frete['status'];
                $avaliacao['cliente_nome'] = $frete['cliente_nome'];
                $avaliacao['fretista_nome'] = $frete['fretista_nome'];
            }
        }
        
        return array_values($result);
    }
}
?>
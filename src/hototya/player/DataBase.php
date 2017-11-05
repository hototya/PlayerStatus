<?php
namespace hototya\player;

class Database
{
    private $db;

    public function __construct(string $dbFile)
    {
        if (!file_exists($dbFile)) {
            $this->db = new \SQLite3($dbFile, SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
        } else {
            $this->db = new \SQLite3($dbFile, SQLITE3_OPEN_READWRITE);
        }
        $this->db->exec('CREATE TABLE IF NOT EXISTS player (
            name TEXT NOT NULL PRIMARY KEY,
            comment TEXT NOT NULL,
            kill INTEGER NOT NULL,
            death INTEGER NOT NULL,
            first TEXT NOT NULL,
            last TEXT NOT NULL
        )');
    }

    public function __destruct()
    {
        $this->db->close();
    }

    /**
     * プレイヤーデータを取得
     * できなければnullを返す
     *
     * @param string $name
     * @return array | null
     */
    public function getData(string $name): ?array
    {
        $sql = 'SELECT * FROM player WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $rows = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        if (!empty($rows)) {
            return $rows;
        } else {
            return null;
        }
    }

    /**
     * プレイヤーデータを作成
     *
     * @param string $name
     */
    public function dataRegister(string $name)
    {
        $time = date("Y/m/d (D)", time());
        $sql = 'INSERT INTO player (name, comment, kill, death, first, last) VALUES (:name, :comment, :kill, :death, :first, :last)';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->bindValue(':comment', "宜しくお願いします。", SQLITE3_TEXT);
        $stmt->bindValue(':kill', 0, SQLITE3_INTEGER);
        $stmt->bindValue(':death', 0, SQLITE3_INTEGER);
        $stmt->bindValue(':first', $time, SQLITE3_TEXT);
        $stmt->bindValue(':last', $time, SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * プレイヤーのコメントを設定
     *
     * @param string $name
     * @param string $comment
     */
    public function setComment(string $name, string $comment)
    {
        $sql = 'UPDATE player SET comment = :comment WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':comment', $comment, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * プレイヤーのキル数を取得
     *
     * @param string $name
     * @return int
     */
    public function getKill(string $name): int
    {
        $sql = 'SELECT kill FROM player WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result['kill'];
    }

    /**
     * プレイヤーのキル数を設定
     *
     * @param string $name
     * @param int    $kill
     */
    public function setKill(string $name, int $kill)
    {
        $sql = 'UPDATE player SET kill = :kill WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':kill', $kill, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     * プレイヤーのデス数を取得
     *
     * @param string $name
     * @return int
     */
    public function getDeath(string $name): int
    {
        $sql = 'SELECT death FROM player WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $result = $stmt->execute()->fetchArray(SQLITE3_ASSOC);
        return $result['death'];
    }

    /**
     * プレイヤーのデス数を設定
     *
     * @param string $name
     * @param int    $death
     */
    public function setDeath(string $name, int $death)
    {
        $sql = 'UPDATE player SET death = :death WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':death', $death, SQLITE3_INTEGER);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }

    /**
     *　プレイヤーの最終プレイ日を更新
     *
     * @param string $name
     */
    public function updateLastPlayed(string $name)
    {
        $time = date("Y/m/d (D)", time());
        $sql = 'UPDATE player SET last = :last WHERE name = :name';
        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':last', $time, SQLITE3_TEXT);
        $stmt->bindValue(':name', $name, SQLITE3_TEXT);
        $stmt->execute();
    }
}

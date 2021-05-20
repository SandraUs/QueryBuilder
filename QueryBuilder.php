<?php

class QueryBuilder {

	protected $pdo;

	public function __construct($pdo)
	{
		$this->pdo = $pdo;
	}

	public function insert($table, $fields = [])
    {
        $values = '';
        foreach ($fields as $item) {
            $values .= "?,";
        }
        $sql = "INSERT INTO {$table} (`" . implode('`, `', array_keys($fields)) . "`) VALUES (" . rtrim($values, ",") . ")";

        if (!$this->query($sql, $fields)->error()) {
            return true;
        }
        return false;
    }

    private function query(string $sql, $params = [])
    {
        $this->error = false;
        $this->query = $this->pdo->prepare($sql);

        if (count($params)) {
            $i = 1;
            foreach ($params as $param) {
                $this->query->bindValue($i, $param);
                $i++;
            }
        }

        if (!$this->query->execute()) {
            $this->error = true;
        } else {
            $this->results = $this->query->fetchAll(PDO::FETCH_OBJ);
            $this->count = $this->query->rowCount();
        }
        return $this;
    }

	
	public function get($table, $where = [])
    {
        return $this->action('SELECT *', $table, $where);
    }

	public function create($table, $data)
	{
		$keys = implode(',', array_keys($data));
		$tags = ":" . implode(', :', array_keys($data));

		$sql = "INSERT INTO {$table} ({$keys}) VALUES ({$tags})";

		$statement = $this->pdo->prepare($sql);
		$statement->execute($data);
	}

	public function update($table, $data, $id)
	{
		$keys = array_keys($data);

		$string = '';

		foreach($keys as $key){
			$string .= $key . '=:' . $key . ',';
		}

		$keys = rtrim($string, ',');

		$data['id'] = $id;

		$sql = "UPDATE {$table} SET {$keys} WHERE id=:id";
		$statement = $this ->pdo->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute($data);

	}

	public function delete($table, $id)
	{
		$sql = "DELETE FROM {$table} WHERE id=:id";

		$statement = $this->pdo->prepare($sql);
		$statement->bindValue(':id', $id);
		$statement->execute();
	}

}
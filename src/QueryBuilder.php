<?php
/**
*	@class Fabacks\QueryBuilder
*	@description Classe construction de requette SQL
*	@author Fabien COLAS
*	@site  dahoo.Fr
*	@gith https://github.com/Fabacks
*	@Copyright Licence CC-by-nc-sa 
*	@Update : 16/09/2019
*	@version 1.0.0
*/
namespace Fabacks;
class QueryBuilder {
    private $fields = array('*');
    private $from;
    private $joins = array();
    private $where = "";
    private $params = array();    
    private $order = array();
    private $limit;
    private $offset;

    /**
     * Selection des éléments
     *
     * @param string|array ...$fields
     * @return self
     */
    public function select(...$fields): self
    {
        if( is_array($fields[0]) ) {
            $fields = $fields[0];
        }   

        $this->fields = ($this->fields === ['*'] ? $fields : array_merge($this->fields, $fields) );
       
        return $this;
    }

    /**
     * Table de séléction
     *
     * @param string $table Le nom de la table
     * @param string $alias L'alias de la table
     * @return self
     */
    public function from(string $table, string $alias = null): self
    {
        $this->from = ($alias === null ? $table : "$table AS $alias");
        return $this;
    }

    /**
     * Jointure de table
     *
     * @param string $join type de jointure, valeur possible => "INNER", "CROSS", "LEFT", "RIGHT", "FULL", "SELF", "NATURAL"
     * @param string $table Table de la jointure
     * @param string $alias alias possible, peut etre null ou string vide si pas d'alias
     * @param string $onLeft jointure gauche
     * @param string $onRight jointure  droite 
     * @return self
     */
    public function join(string $join, string $table, string $alias, string $onLeft, string $onRight): self
    {
        $join = strtoupper($join);
        if( !in_array($join, array("INNER", "CROSS", "LEFT", "RIGHT", "FULL", "SELF", "NATURAL")) ){
            $join = "INNER";
        }

        $this->joins[] = array(
            "join"    => $join,
            "table"   => $table,
            "alias"   => $alias,
            "onLeft"  => $onLeft,
            "onRight" => $onRight,
        );
        return $this;
    }

        /**
     * Ajoute une|des clausse where
     *
     * @param string $where
     * @return self
     */
    public function where(string $where): self 
    {
        $this->where .= $where;
        return $this;
    }

    /**
     * Remplace les parametres dans la clausse where 
     *
     * @param string $key
     * @param [type] $value
     * @return self
     */
    public function setParam(string $key, $value): self 
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Organisation des données
     *
     * @param string $key champs
     * @param string $direction direction ASC | DESC
     * @return self
     */
    public function orderBy(string $key, string $direction = "ASC"): self 
    {
        $direction = strtoupper($direction);
        if( in_array($direction, array('ASC', 'DESC')) ){
            $this->order[] = "$key $direction";
        } else {
            $this->order[] = $key;
        }
        
        return $this;
    }

    /**
     * Limite des données
     *
     * @param integer $limit
     * @return self
     */
    public function limit(int $limit): self 
    {
        $this->limit = $limit;
        
        return $this;
    }

    /**
     * Offeset des données
     *
     * @param integer $offset
     * @return self
     */
    public function offset(int $offset): self 
    {
        $this->offset = $offset;
        
        return $this;
    }

    /**
     * Calcule automatiquement l'offset en fonction de la limit
     *
     * @param integer $page
     * @return self
     */
    public function page(int $page): self 
    {
        return $this->offset($this->limit * ($page - 1));
    }


    /**
     * Rend la chaine SQL en string
     *
     * @return string
     */
    public function toSQL(): string 
    {
        $fields = implode(', ', $this->fields);
        $sql = "SELECT $fields FROM {$this->from}"; 

        if( count($this->joins) > 0) {
            foreach($this->joins as $key => $join):
                $sql .= ' '.$join["join"].' JOIN '.$join['table']; 
                $sql .= ( !empty($join['alias']) ? ' AS '.$join['alias'] : '');
                $sql .= ' ON '.$join['onLeft'].' = '.$join['onRight'];
            endforeach;
        }

        if( $this->where ) {
            $where = $this->where;
            if( count($this->params) > 0) {
                foreach($this->params as $key => $value):
                    $where = str_replace(':'.$key, $value, $where);
                endforeach;
            }

            $sql .= " WHERE ". $where;
        }

        if( !empty($this->order) ){
            $sql .= " ORDER BY ".implode(', ', $this->order);
        }

        if( $this->limit > 0) {
            $sql .= " LIMIT ".$this->limit; 
        }

        if( $this->offset !== null ) {
            $sql .= " OFFSET ".$this->offset; 
        }        

        return $sql;
    }

    function __toString()
    {
        return self::toSQL();
    }
}
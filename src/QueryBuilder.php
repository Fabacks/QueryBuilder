<?php
/**
*	@class Fabacks\QueryBuilder
*	@description Classe construction de requête SQL
*	@author Fabien COLAS
*	@site  dahoo.Fr
*	@git https://github.com/Fabacks
*	@Copyright Licence CC-by-nc-sa 
*	@Update : 12/01/2025
*   @PHP min : 7.1
*	@version 1.5.0
*/
namespace Fabacks;

use Exception;

class QueryBuilder
{
    private $fields = array('*');
    private $from   = "";
    private $joins  = array();
    private $where  = "";
    private $params = array();
    private $order  = array();
    private $group  = array();
    private $having = "";
    private $limit  = 0;
    private $offset = null;


    /**
     * Selection des éléments
     *
     * @param array string ...$fields
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
     * Réinitialise la selection des éléments par défaut '*'
     *
     * @return self
     */
    public function selectClear(): self
    {
        $this->fields = array('*');
        return $this;
    }

    /**
     * Table de sélection
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
     * @param string $alias alias possible, peut être null ou string vide si pas d'alias
     * @param string $onLeft jointure gauche
     * @param string $onRight jointure  droite 
     * @return self
     */
    public function join(string $join, string $table, string $alias, string $onLeft, string $onRight, string $typeJoin = '='): self
    {
        $join = strtoupper($join);
        if( !in_array($join, array("INNER", "CROSS", "LEFT", "RIGHT", "FULL", "SELF", "NATURAL")) )
            throw new Exception("Type of join is not valid : $join");

        $this->joins[] = array(
            'join'     => $join,
            'table'    => $table,
            'alias'    => $alias,
            'onLeft'   => $onLeft,
            'onRight'  => $onRight,
            'typeJoin' => $typeJoin,
        );
        return $this;
    }

    /**
     * Ajoute une|des clause where. La concaténation par défaut est un "AND"
     *
     * @param string $where La condition
     * @param string $append (Optionnel) Ajoute le type de concaténation automatiquement
     * @return self
     */
    public function where(string $where, $append = null): self 
    {
        $list = array("AND", "OR");
        $append = $append == null ? '' : strtoupper($append);
        if( $append != null && in_array($append, $list) ):
            $this->where .= ' '.$append.' '.$where;
        else :
            $this->where .= (empty($this->where) ? '' : " AND ").$where;
        endif;

        return $this;
    }

    /**
     * Remplace les paramètres dans la clause where 
     *
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function setParam(string $key, $value): self 
    {
        $this->params[$key] = $value;
        return $this;
    }

    /**
     * Permet de faire un GROUP BY
     *
     * @param string $key champs
     * @return self
     */
    public function groupBy(string $key): self 
    {
        $this->group[] = $key;
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
     * Ajoute une|des clause having
     *
     * @param string $pHaving La condition
     * @param string $pAppend (Optionnel) Ajoute le type de concaténation automatiquement
     * @return self
     */
    public function having(string $pHaving, $pAppend = null): self 
    {
        $list = array("AND", "OR");
        $append = $pAppend === null ? null : strtoupper($pAppend);
        if( $append != null && in_array($append, $list) ):
            $this->having .= ' '.$append.' '.$pHaving;
        else :
            $this->having .= $pHaving;
        endif;

        return $this;
    }

    /**
     * Limite des données
     *
     * @param integer $limit
     * @param integer $offset
     * @return self
     */
    public function limit(int $limit, int $offset = null): self 
    {
        $this->limit = $limit;
        if( $offset != null )
            $this->offset = $offset;

        return $this;
    }

    /**
     * Offset des données
     *
     * @param integer $offset
     * @return self
     */
    public function offset(?int $offset): self 
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
        $offset = ($this->limit * $page) - $this->limit;
        $offset = $offset < 0 ? 0 : $offset; 
        return $this->offset($offset);
    }


    /**
     * Rend la chaine SQL en string
     *
     * @return string
     */
    public function toSQL(): string 
    {
        // Partie SELECT
        $sql = $this->buildSelect();

        // Partie FROM
        $sql .= ' FROM '.$this->from;

        // Partie JOINS
        if( count($this->joins) > 0 ):
            $sql .= $this->buildJoins();
        endif;

        // Partie WHERE
        if( $this->where ):
            $sql .= ' WHERE '. $this->buildWhere();
        endif;

        // Partie GROUP
        if( !empty($this->group) ):
            $sql .= " GROUP BY ".implode(', ', $this->group);
        endif;

        // Partie ORDRER BY
        if( !empty($this->order) ):
            $sql .= " ORDER BY ".implode(', ', $this->order);
        endif;

        // Partie HAVING
        if( !empty($this->having) ):
            $sql .= " HAVING ". $this->having;
        endif;

        // Partie LIMIT
        if( $this->limit > 0 ):
            $sql .= " LIMIT ".$this->limit; 
        endif;

        // Partie OFFSET
        if( $this->offset !== null ):
            $sql .= " OFFSET ".$this->offset; 
        endif;

        return $sql.';';
    }

    /**
     * Rend la chaine SQL et les paramètres en array
     * @return array 
     */
    public function toSQLWithParams()
    {
        return [
            'query' => $this->toSQL(),
            'params' => $this->params,
        ];
    }

    /**
     * Rend la chaine SELECT
     * @return string 
     */
    private function buildSelect()
    {
        return "SELECT ".implode(', ', $this->fields);
    }

    private function buildJoins(): string 
    {
        $sql = '';

        foreach($this->joins as $key => $join):
            $sql .= ' '.$join["join"].' JOIN '.$join['table']; 
            $sql .= ( !empty($join['alias']) ? ' AS '.$join['alias'] : '');
            $sql .= ' ON '.$join['onLeft'].' '.$join['typeJoin'].' '.$join['onRight'];
        endforeach;

        return $sql;
    }

    private function buildWhere(): string
    {
        $where = $this->where;
        if( count($this->params) > 0 ):
            foreach($this->params as $key => $value):
                $where = str_replace(':'.$key, $value, $where);
            endforeach;
        endif;

        return $where;
    }

    function __toString()
    {
        return $this->toSQL();
    }

}
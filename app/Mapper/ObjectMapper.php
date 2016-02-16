<?php

namespace MP\Mapper;

use MP\Object\ObjectMetadata;
use MP\Service\FilterService;

/**
 * Mapper pro objekty
 * Z duvodu optimalizace maji nektere akce vlastni dotaz
 */
class ObjectMapper extends AbstractLangAwareDatabaseMapper
{
    /** @const Nazev sloupce s ID objektu. */
    const OBJECT_ID = 'object_id';
    /** @const Nazev sloupce s ID puvodniho objektu. */
    const PARENT_OBJECT_ID = 'parent_object_id';

    /**
     * Vrati min. mnozinu dat nutnou k vykresleni markeur na mape
     *
     * @param array|null $restrictor
     *
     * @return \Dibi\Row[]|null
     * @throws \Dibi\Exception
     */
    public function selectMarkers($restrictor = null)
    {
        $result = null;

        $query = [
            "
                SELECT
                    [object_id],
                    [ruian_address],
                    [longitude],
                    [latitude],
                    (
                        SELECT [title]
                        FROM [map_object_lang]
                        WHERE
                            [map_object_id] = main.[id]
                            AND NULLIF([title], '') IS NOT NULL
                        ORDER BY lang_id = %s DESC
                        LIMIT 1
                    ) as [title],
                    [object_type_id],
                    [accessibility_id],
                    CASE WHEN [certified] THEN
                        CASE WHEN ([mapping_date] IS NULL OR [mapping_date] <= (CURRENT_TIMESTAMP - INTERVAL '3 years'))
                            THEN %s
                            ELSE %s
                        END
                        ELSE %s
                    END AS [type]
                FROM %n main
            ",
            $this->lang->getLang(),
            FilterService::TYPE_OUTDATED,
            FilterService::TYPE_CERTIFIED,
            FilterService::TYPE_COMMUNITY,
            $this->table,
        ];

        $this->buildWhere($restrictor, $query);
        $result = $this->executeQuery($query)->fetchAll();

        return $result;
    }

    /**
     * Vrati IDcka objektu.
     *
     * @param array|null $restrictor
     *
     * @return \Dibi\Row[]
     * @throws \Dibi\Exception
     */
    public function selectIds($restrictor = null)
    {
        $query = ["SELECT [id] FROM %n", $this->table];
        $this->buildWhere($restrictor, $query);

        $result = $this->executeQuery($query)->fetchAll();

        return $result;
    }

    /**
     * Vrati napovedu pro vyber objektu.
     *
     * @param array|null $restrictor
     *
     * @return \Dibi\Row[]
     * @throws \Dibi\Exception
     */
    public function selectSuggestions($restrictor = null)
    {
        $query = ["
            SELECT
                [id],
                [object_id],
                (
                    SELECT [title]
                    FROM [map_object_lang]
                    WHERE
                        [map_object_id] = main.[id]
                        AND NULLIF([title], '') IS NOT NULL
                    ORDER BY lang_id = %s DESC
                    LIMIT 1
                ) as [title],
                [zipcode],
                [city],
                [city_part],
                [street],
                [street_desc_no],
                [street_orient_no],
                [street_orient_symbol]
          FROM %n main
        ", $this->lang->getLang(), $this->table];
        $this->buildWhere($restrictor, $query);

        $result = $this->executeQuery($query)->fetchAll();

        return $result;
    }

    /**
     * @return string
     */
    protected function getDataKeyColumn()
    {
        return 'map_object_id';
    }

    /**
     * @return array
     */
    protected function getLangAwareColumns()
    {
        return ObjectMetadata::$LANG_AWARE_COLUMNS[ObjectMetadata::OBJECT];
    }

    /**
     * Provede zaverzovani zaznamu.
     *
     * @param int $id
     */
    public function backup($id)
    {
        $query = ["SELECT object_backup(%i)", $id];

        $this->connection->query($query);
    }

    /**
     * @param int $id
     * @return \Dibi\Result|int
     */
    public function deleteById($id)
    {
        $query = ["SELECT delete_object(%i)", $id];

        $result = $this->executeQuery($query);

        return $result;
    }

    /**
     * Provede smazani zaznamu.
     *
     * @param int $id
     *
     * @return \Dibi\Result|int
     */
    public function deleteBySource($id)
    {
        $query = ["SELECT delete_object_by_source(%i)", $id];

        $result = $this->executeQuery($query);

        return $result;
    }

    /**
     * Vrati pocet objektu seskupenych podle regionu
     * Objekty bez regionu ignoruji
     * @return \Dibi\Row[]|null
     */
    public function selectRegionsStats()
    {
        $result = null;

        $query = [
            "
                SELECT [region], count(*) AS [count]
                FROM %n
                WHERE NULLIF([region], '') IS NOT NULL
                GROUP BY [region]
                ORDER BY [region]
            ",
            $this->table,
        ];

        return $this->executeQuery($query)->fetchAll();
    }

    /**
     * @param int $id
     *
     * @return \Dibi\Result|int
     */
    public function revert($id)
    {
        $query = ["SELECT object_version_revert(%i)", $id];

        $result = $this->executeQuery($query);

        return $result;
    }

    /**
     * @param int $id
     *
     * @return int
     */
    public function split($id)
    {
        $query = ["SELECT object_version_split(%i)", $id];

        $result = $this->executeQuery($query);

        return $result->fetchSingle();
    }

    /**
     * @param int $source
     * @param int $destination
     *
     * @return int
     */
    public function join($source, $destination)
    {
        $query = ["SELECT object_merge(%i, %i)", $source, $destination];

        $result = $this->executeQuery($query);

        return $result->fetchSingle();
    }
}

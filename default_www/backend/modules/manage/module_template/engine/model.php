/**
 * Backend{$module.name|camelcase}Model
 * In this file we store all generic functions that we will be using in the {$module.name} module.
 *
 * @package		backend
 * @subpackage	{$module.name}

 *
 * @author		{$user.name} <{$user.email}>
 * @since		{$module.fork_version}

 */
class Backend{$module.name|camelcase}Model
{
	const QRY_BROWSE = 'SELECT i.id, i.name
						FROM {$module.name} AS i;';


	/**
	 * Deletes one or multiple records
	 *
	 * @return	void
	 * @param	mixed $ids		The ID(s) of the record(s) to delete.
	 */
	public static function delete($ids)
	{
		// get db
		$db = BackendModel::getDB(true);

		// $ids is an array
		if(is_array($ids))
		{
			// delete the records
			$db->delete('{$module.name}', 'id IN('. implode(',', $ids) .')');
		}

		// $ids was not an array
		else
		{
			// delete the record
			$db->delete('{$module.name}', 'id = ?', (int) $ids);
		}
	}


	/**
	 * Checks if a record exists
	 *
	 * @return	bool
	 * @param	int $id		The ID of the record.
	 */
	public static function exists($id)
	{
		// redefine
		$id = (int) $id;

		// get db
		$db = BackendModel::getDB();

		// return the result
		return (bool) ((int) $db->getVar('SELECT COUNT(i.name)
											FROM {$module.name} AS i
											WHERE i.id = ?;',
											array($id)) > 0);
	}


	/**
	 * Checks if a record exists by name
	 *
	 * @return	bool
	 * @param	string $name		The name of the record.
	 */
	public static function existsByName($name)
	{
		// redefine
		$name = (string) $name;

		// get db
		$db = BackendModel::getDB();

		// return the result
		return (bool) ((int) $db->getVar('SELECT COUNT(i.name)
											FROM {$module.name} AS i
											WHERE i.name = ?;',
											array($name)) > 0);
	}


	/**
	 * Get all available records, or a specific record if the parameter ID was set
	 *
	 * @return	array
	 * @param	int[optional] $id
	 */
	public static function get($id = null)
	{
		// get db
		$db = BackendModel::getDB();

		// build query
		$query = 'SELECT i.*
					FROM {$module.name} AS i';

		// build parameters
		$parameters = null;

		// id was set
		if(!empty($id))
		{
			$query .= ' WHERE i.id = ?';
			$parameters = array((int) $id);

			// return result
			return (array) $db->getRecord($query, $parameters);
		}

		// return results
		return (array) $db->getRecords($query, $parameters);
	}


	/**
	 * Adds a new record. Returns the insert-ID of the record.
	 *
	 * @return	int
	 * @param	array $record		The record to insert.
	 */
	public static function insert(array $record)
	{
		// get db
		$db = BackendModel::getDB(true);

		// insert record
		return (int) $db->insert('{$module.name}', $record);
	}


	/**
	 * Save the changes for a given record. Returns the amount of updated rows.
	 * Remark: $record['id'] should be available
	 *
	 * @return	void
	 * @param	array $record		The record to update.
	 */
	public static function update(array $record)
	{
		// get db
		$db = BackendModel::getDB(true);

		// insert record
		return (int) $db->update('{$module.name}', $record, 'id = ?', array((int) $record['id']));
	}
}
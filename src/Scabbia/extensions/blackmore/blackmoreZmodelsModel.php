<?php	namespace Scabbia\Extensions\Blackmore;	/**	 * @ignore	 */	class blackmoreZmodelModel extends model {		/**		 * @param $uTable		 * @param $uInput		 *		 * @return mixed		 */		public function insert($uTable, $uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()					->setTable($uTable)					->setFields($uInput)					->addField('categoryid', string::generateUuid())					->addField('createdate', $tTime)					->addField('updatedate', $tTime)					->setReturning('categoryid')					->insert()					->execute();		}		/**		 * @param $uTable		 * @param $uCategoryId		 * @param $uInput		 *		 * @return mixed		 */		public function update($uTable, $uCategoryId, $uInput) {			$tTime = time::toDb(time());			return $this->db->createQuery()					->setTable($uTable)					->setFields($uInput)					->addField('updatedate', $tTime)					->addParameter('categoryid', $uCategoryId)					->setWhere('categoryid=:categoryid')					->andWhere('deletedate IS NULL')					->setLimit(1)					->update()					->execute();		}		/**		 * @param $uTable		 * @param $uCategoryId		 *		 * @return mixed		 */		public function deletePhysically($uTable, $uCategoryId) {			return $this->db->createQuery()					->setTable($uTable)					->addParameter('categoryid', $uCategoryId)					->setWhere('categoryid=:categoryid')					->setLimit(1)					->delete()					->execute();		}		/**		 * @param $uTable		 * @param $uCategoryId		 *		 * @return mixed		 */		public function delete($uTable, $uCategoryId) {			$tTime = time::toDb(time());			return $this->db->createQuery()					->setTable($uTable)					->addField('deletedate', $tTime)					->addParameter('categoryid', $uCategoryId)					->setWhere('categoryid=:categoryid')					->andWhere('deletedate IS NULL')					->setLimit(1)					->update()					->execute();		}		/**		 * @param $uTable		 * @param $uSlug		 *		 * @return mixed		 */		public function deleteBySlug($uTable, $uSlug) {			$tTime = time::toDb(time());			return $this->db->createQuery()					->setTable($uTable)					->addField('deletedate', $tTime)					->addParameter('slug', $uSlug)					->setWhere('slug=:slug')					->andWhere('deletedate IS NULL')					->setLimit(1)					->update()					->execute();		}		/**		 * @param $uTable		 * @param $uCategoryId		 *		 * @return mixed		 */		public function get($uTable, $uCategoryId) {			return $this->db->createQuery()					->setTable($uTable)					->addField('*')					->addParameter('categoryid', $uCategoryId)					->setWhere('categoryid=:categoryid')					->andWhere('deletedate IS NULL')					->setLimit(1)					->get()					->row();		}		/**		 * @param $uTable		 * @param $uSlug		 *		 * @return mixed		 */		public function getBySlug($uTable, $uSlug) {			return $this->db->createQuery()					->setTable($uTable)					->addField('*')					->addParameter('slug', $uSlug)					->setWhere('slug=:slug')					->andWhere('deletedate IS NULL')					->setLimit(1)					->get()					->row();		}		/**		 * @param $uTable		 *		 * @return mixed		 */		public function getAll($uTable) {			return $this->db->createQuery()					->setTable($uTable)					->addField('*')					->setWhere('deletedate IS NULL')					->setOrderBy('createdate', 'DESC')					->get()					->all();		}		/**		 * @param $uTable		 * @param $uType		 *		 * @return mixed		 */		public function getAllByType($uTable, $uType) {			return $this->db->createQuery()					->setTable($uTable)					->addField('*')					->addParameter('type', $uType)					->setWhere('deletedate IS NULL')					->andWhere('type=:type')					->setOrderBy('createdate', 'DESC')					->get()					->all();		}		/**		 * @param $uTable		 *		 * @return array		 */		public function getAllAsPairs($uTable) {			$tQuery = $this->db->createQuery()					->setTable($uTable)					->addField('*')					->setWhere('deletedate IS NULL')					->setOrderBy('createdate', 'DESC')					->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		/**		 * @param $uTable		 * @param $uType		 *		 * @return array		 */		public function getAsPairs($uTable, $uType) {			$tQuery = $this->db->createQuery()					->setTable($uTable)					->addField('*')					->addParameter('type', $uType)					->setWhere('deletedate IS NULL')					->andWhere('type=:type')					->setOrderBy('createdate', 'DESC')					->get();			$tArray = array();			foreach($tQuery as $tRow) {				$tArray[$tRow['categoryid']] = $tRow;			}			$tQuery->close();			return $tArray;		}		/**		 * @param $uTable		 *		 * @return mixed		 */		public function count($uTable) {			return $this->db->calculate($uTable, 'COUNT', '*', 'deletedate IS NULL');		}	}	?>
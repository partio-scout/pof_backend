<?php
namespace POFTREE {

	class basedetails {
		public $guid;
		public $title;

		public $lastModified;
		public $lastModifiedBy;

		public $languages = array();
	}

	class lastModifiedBy {
		public $name;
		public $id;
	}

	class programs {
		public $program = array();
	}

	class program extends basedetails {
		public $owner;
		public $lang;
		public $treeDetails;
		public $agegroups = array();
	}

	class agegroup extends basedetails {
		public $minAge;
		public $maxAge;
		public $subtaskgroup_term;
		public $taskgroups = array();
	}

	class taskgroup extends basedetails {
		public $taskgroups = array();
		public $tasks = array();
		public $additional_tasks_count;
		public $subtaskgroup_term;
		public $taskgroup_term;
		public $subtask_term;
		public $mandatory_tasks;
	}

	class task extends basedetails {
		public $suggestions_details = array();
		public $task_term;
	}
}

namespace POFITEM {
	class basedetails {
		public $type;
		public $guid;
		public $title;
		public $ingress;
		public $content;

		public $lastModified;
		public $lastModifiedBy;

		public $languages = array();
		public $lang;

		public $tags;

		public $images;
		public $additional_content = array();
	}

	class lastModifiedBy {
		public $name;
		public $id;
	}

	class program extends basedetails {
		public $owner;
		public $lang;
	}

	class agegroup extends basedetails {
		public $minAge;
		public $maxAge;
		public $subtaskgroup_term;
	}

	class taskgroup extends basedetails {
		public $additional_tasks_count;
		public $mandatory_tasks;
		public $subtaskgroup_term;
		public $taskgroup_term;
		public $subtask_term;
	}

	class task extends basedetails {
		public $mandatory;
		public $mandatory_seascouts;
		public $groupsize;
		public $place_of_performance;
		public $suggestions_details = array();
		public $task_term;
	}

}
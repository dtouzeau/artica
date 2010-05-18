<?php
$_GLOBAL["usb_list"]=array(
		"ca495d83-fdc9-46f1-9205-7412014e1d93"=>array(
			"PATH"=>"/dev/sda1",
			"UUID"=>"ca495d83-fdc9-46f1-9205-7412014e1d93",
			"LABEL"=>"",
			"TYPE"=>"ext4",
			"SEC_TYPE"=>"",
			"model"=>";;;;:;Mbps;/sbin/udevadm info --query=all --path=`/sbin/udevadm info --query=path --name=/dev/sda1`",
			"mounted"=>"/",
			"DEV"=>"sda1",
			"ID_MODEL"=>"Hitachi_HTS542516K9A300",
			"ID_VENDOR"=>"",
			"ID_FS_LABEL"=>"",
			"DEVTYPE"=>"partition",
			"ID_FS_TYPE"=>"ext4",
			"ID_FS_LABEL_SAFE"=>"",
			"SIZE"=>"141G;28G;106G;21%"),
		"d94fe4f9-8543-48d7-bce2-46ecc52ea9f5"=>array(
			"PATH"=>"/dev/sdb1",
			"UUID"=>"d94fe4f9-8543-48d7-bce2-46ecc52ea9f5",
			"LABEL"=>"vmwares",
			"TYPE"=>"ext4",
			"SEC_TYPE"=>"",
			"model"=>";;;;:;Mbps;/sbin/udevadm info --query=all --path=`/sbin/udevadm info --query=path --name=/dev/sdb1`",
			"mounted"=>"/media/hd2",
			"DEV"=>"sdb1",
			"ID_MODEL"=>"Hitachi_HTS542516K9A300",
			"ID_VENDOR"=>"",
			"ID_FS_LABEL"=>"vmwares",
			"DEVTYPE"=>"partition",
			"ID_FS_TYPE"=>"ext4",
			"ID_FS_LABEL_SAFE"=>"",
			"SIZE"=>"147G;67G;74G;48%"),
		);


// Disks list...

$_GLOBAL["disks_list"]=array(
		"/dev/sda"=>array(
			"SIZE"=>"160.0 GB",
			"ID_FS_LABEL"=>"",
			"ID_MODEL_1"=>"",
			"ID_MODEL_2"=>"Hitachi_HTS542516K9A300",
			"ID_VENDOR"=>"",
			"ID_BUS"=>"ata",
			"MOUNTED"=>"",
			"PARTITIONS"=>array(
				"/dev/sda1"=>array(
					"TYPE"=>"83",
					"MOUNTED"=>"/",
					"ID_FS_LABEL"=>"",
					"ID_FS_TYPE"=>"",
					"free_size"=>"141G;28G;106G;21%",
					"SIZE"=>"153.5 GB"
				),
				"/dev/sda2"=>array(
					"TYPE"=>"5",
					"MOUNTED"=>"/",
					"ID_FS_LABEL"=>"",
					"ID_FS_TYPE"=>"",
					"free_size"=>"",
				),
				"/dev/sda5"=>array(
					"TYPE"=>"82",
					"MOUNTED"=>"/",
					"ID_FS_LABEL"=>"",
					"ID_FS_TYPE"=>"",
					"free_size"=>"",
					"SIZE"=>"6539 MB"
				),
			)

			),
		"size (logical/physical)"=>array(
			"SIZE"=>"512 bytes",
			"ID_FS_LABEL"=>"",
			"ID_MODEL_1"=>"",
			"ID_MODEL_2"=>"",
			"ID_VENDOR"=>"",
			"ID_BUS"=>"ata",
			"MOUNTED"=>"",
			"PARTITIONS"=>array()
			),
		"/dev/sdb"=>array(
			"SIZE"=>"160.0 GB",
			"ID_FS_LABEL"=>"",
			"ID_MODEL_1"=>"",
			"ID_MODEL_2"=>"Hitachi_HTS542516K9A300",
			"ID_VENDOR"=>"",
			"ID_BUS"=>"ata",
			"MOUNTED"=>"",
			"PARTITIONS"=>array(
				"/dev/sdb1"=>array(
					"TYPE"=>"83",
					"MOUNTED"=>"/media/hd2",
					"ID_FS_LABEL"=>"vmwares",
					"ID_FS_TYPE"=>"",
					"free_size"=>"147G;67G;74G;48%",
					"SIZE"=>"160.0 GB"
				),
			)

			),
		"size (logical/physical)"=>array(
			"SIZE"=>"512 bytes",
			"ID_FS_LABEL"=>"",
			"ID_MODEL_1"=>"",
			"ID_MODEL_2"=>"",
			"ID_VENDOR"=>"",
			"ID_BUS"=>"ata",
			"MOUNTED"=>"",
			"PARTITIONS"=>array()
			),
	);


// lvm list...




// lvm group list...


?>

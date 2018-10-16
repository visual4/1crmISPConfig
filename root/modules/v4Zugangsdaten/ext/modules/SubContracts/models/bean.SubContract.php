<?php return; ?>

links
    zugangsdaten
		relationship: subcontract_zugangsdaten
		vname: LBL_ZUGANGSDATEN


relationships
    subcontract_zugangsdaten
		key: id
		target_bean: v4Zugangsdaten
		target_key: service_subcontract_id
		relationship_type: one-to-many
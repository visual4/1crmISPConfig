<?php return; ?>

links
    ispc_sites
		relationship: subcontracts_ispc_sites
		bean_name: ISPConfig
		vname: LBL_ISPC_SITES_SUBPANEL_TITLE
        add_existing: true

relationships
	subcontracts_ispc_sites
		key: id
		target_bean: ISPConfig
		target_key: service_contract_id
		relationship_type: one-to-many
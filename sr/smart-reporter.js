// Floating notification start
Ext.notification = function(){
	var msgCt;
	function createBox(t, s){
		return ['<div class="msg">',
		'<div class="x-box-tl"><div class="x-box-tr"><div class="x-box-tc"></div></div></div>',
		'<div class="x-box-ml"><div class="x-box-mr"><div class="x-box-mc"><h3>', t, '</h3>', s, '</div></div></div>',
		'<div class="x-box-bl"><div class="x-box-br"><div class="x-box-bc"></div></div></div>',
		'</div>'].join('');
	}
	return {
		msg : function(title, format){
			if(!msgCt){
				msgCt = Ext.core.DomHelper.insertFirst(document.body, {id:'msg-div'}, true);
			}

			Ext.Element = msgCt;
			msgCt = Ext.Element;
			msgCt.alignTo(document, 't-t');
			var s = Ext.String.format.apply(String, Array.prototype.slice.call(arguments, 1));
			var m = Ext.core.DomHelper.append(msgCt, {html:createBox(title, s)}, true);
			m.slideIn('t').pause(1000).ghost("t", {remove:true});
		},

		init : function(){
			var lb = Ext.get('lib-bar');
			if(lb){
				lb.show();
			}
		}
	};
}();
// Floating notification end

Ext.onReady(function() {
	
	SR.searchTextField 	  = '';	
	var now 		      = new Date();
	var lastMonDate       = new Date(now.getFullYear(), now.getMonth() - 1, now.getDate() + 1);
	var search_timeout_id = 0; //timeout for sending request while searching.
	var dateFormat        = 'M d Y';
	
	SR.searchTextField = new Ext.form.field.Text({
		id: 'tf',
		width: 250,
		cls: 'searchPanel',
		style: {
			fontSize: '14px',
			paddingLeft: '2px',
			width: '100%'
		},
		params: {
			cmd: 'searchText'
		},
		emptyText: 'Search...',
		enableKeyEvents: true,
		listeners: {
			keyup: function () {

				// make server request after some time - let people finish typing their keyword
				clearTimeout(search_timeout_id);
				search_timeout_id = setTimeout(function () {					
					gridPanelSearchLogic();
				}, 500);
			}}
	});
		
	SR.fromDateField = new Ext.form.field.Date({
		fieldLabel: 'From',
		labelWidth : 35,
		emptyText : 'From Date',
		format: dateFormat,
		width: 150,
		maxValue: now,
		listeners: {
			select: function ( t, value ){
				smartDateComboBox.reset();
				t.setValue(value);
				getSales();
			}
		}
	});
	
	SR.toDateField = new Ext.form.field.Date({
		fieldLabel: 'To',
		labelWidth : 20,
		emptyText : 'To Date',
		format: dateFormat,
		width: 150,
		maxValue: now,
		value: now,
		listeners: {
			select: function ( t, value ){
				smartDateComboBox.reset();
				t.setValue(value);
				getSales();
			}
		}
	});
	
	// to limit Lite version to available days
	SR.checkFromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - availableDays);
	SR.checkToDate   = now;

	var smartDateComboBox = Ext.create('Ext.form.ComboBox', {
		queryMode: 'local',
		width : 100,
		store: new Ext.data.ArrayStore({
			autoDestroy: true,
			forceSelection: true,
			fields: ['value', 'name'],
			data: [
					['TODAY',      'Today'],
					['YESTERDAY',  'Yesterday'],
					['THIS_WEEK',  'This Week'],
					['LAST_WEEK',  'Last Week'],
					['THIS_MONTH', 'This Month'],
					['LAST_MONTH', 'Last Month'],
					['3_MONTHS',   '3 Months'],
					['6_MONTHS',   '6 Months'],
					['THIS_YEAR',  'This Year'],
					['LAST_YEAR',  'Last Year']
				]
		}),
		displayField: 'name',
		valueField: 'value',
		triggerAction: 'all',
		editable: false,
		emptyText : 'Select Date',
		style: {
			fontSize: '14px',
			paddingLeft: '2px'
		},
		forceSelection: true,
		listeners: {
			select: function () {
				var dateValue = this.value;
				if(fileExists == 0){
					if(smartDateComboBox.getValue() == 'TODAY' || smartDateComboBox.getValue() == 'YESTERDAY' || smartDateComboBox.getValue() == 'THIS_WEEK'){
						liteSelectDate(dateValue);
						getSales();
					}else{
						Ext.notification.msg('Smart Reporter',"Available only in Pro version" );
					}
				}else{
					proSelectDate(dateValue);
					getSales();
				}
			}
		}
	});
	
	var liteSelectDate = function (dateValue){
		var fromDate,toDate;

		switch (dateValue){

			case 'TODAY':
			fromDate = now;
			toDate 	 = now;
			break;

			case 'YESTERDAY':
			fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 1);
			toDate 	 = now;
			break;

			case 'THIS_WEEK':
			fromDate = new Date(now.getFullYear(), now.getMonth(), now.getDate() - (now.getDay() - 1));
			toDate 	 = now;
			break;
			
			default:
			fromDate = new Date(now.getFullYear(), now.getMonth(), 1);
			toDate 	 = now;
			break;
		}

		SR.fromDateField.setValue(fromDate);
		SR.toDateField.setValue(toDate);

		SR.fromDate = fromDate;
		SR.toDate 	= toDate;

		return SR;
	};
	
	var getSales = function(){
		loadGridStore();
	}
			
	// store for graph
	var lineGraphStore = Ext.create('Ext.data.Store', {
		id : 'lineGraphStore',
		autoLoad : false,
		fields : [ {
			name : 'period',
			type : 'string'
		}, {
			name : 'sales',
			type : 'float'
		}

		],
		params : {
			fromDate : SR.fromDateField.getValue(),
			toDate : SR.toDateField.getValue(),
			start : 0,
			cmd : 'getData'
		},
		proxy : {
			type : 'ajax',
			url : jsonURL, // url that will load data with respect to start and
			reader : {
				type : 'json',
				totalProperty : 'totalCount'
				,root : 'items'
			},
			//this will be used in place of BaseParams of extjs 3
			//Extra parameters that will be included on every request which will help us if we use pagination.
			extraParams :{
				searchText: SR.searchTextField.getValue()
			}
		}
	});

	// store for graph function
	var lineGraphStoreLoad = function(id) {
		lineGraphStore.load({
			params : {
				fromDate  : SR.fromDateField.getValue(),
				toDate    : SR.toDateField.getValue(),
				searchText: SR.searchTextField.getValue(),
				start 	  : 0,
				id 		  : id,
				cmd 	  : 'getData'
			}
		});
	};

	lineGraphStore.on('load',
	function() {
		if (lineGraphStore.getTotalCount() == 0) {
			Ext.notification.msg('Info','No sales found');
		} else {
		}
	});
		
	
	// grid store
	var gridStore = Ext.create('Ext.data.Store', {
		id : 'gridStore',
		autoLoad : false,
		fields : [ {
			name : 'id',
			type : 'int'
		}, {
			name : 'products',
			type : 'string'
		},{
			name : 'category',
			type : 'string'
		}, {
			name : 'sales',
			type : 'float'
		}],
		proxy : {
			type : 'ajax',
			url : jsonURL, // url that will load data with respect to start and
			reader : {
				type : 'json',
				totalProperty : 'gridTotalCount'
				,root : 'gridItems'
			}
		},
		params : {
			fromDate : SR.fromDateField.getValue(),
			toDate : SR.toDateField.getValue(),
			start : 0,
			cmd : 'gridGetData'
		},
		extraParams :{
			searchText: SR.searchTextField.getValue()
		},
		listeners : {
			load : function() {				
				var model = gridPanel.getSelectionModel();
				
				if (this.getTotalCount() != 0) {
					model.select(0);
				}
			}
		}
	});

	var loadGridStore = function() {
		gridStore.load({
			params : {
				fromDate : SR.fromDateField.getValue(),
				toDate : SR.toDateField.getValue(),
				start : 0,
				searchText: SR.searchTextField.getValue(),
				cmd : 'gridGetData'
			}
		});
	};
	
	gridStore.on('load',
	function() {
		if (gridStore.getTotalCount() == 0) {
			Ext.notification.msg('Info','No sales found');
		} else {
		}
	});
	
	// create a grid that will list the dataset items.
	var gridPanel = Ext.create('Ext.grid.Panel', {
		autoScroll : true,
		columnLines : true,
		flex : 2,
		store : gridStore,
		columns : [
		{
			text : 'Products',
			width : 200,
			flex : 1.5,
			sortable : true,
			dataIndex : 'products'
		}, {
			text : 'Category',
			width : 150,
			flex : 1,
			sortable : true,
			dataIndex : 'category'
		},{
			text : 'Sales',
			width : 150,
			flex : 0.5,
			align : 'right',
			sortable : true,
			xtype : 'numbercolumn',
			format : '0.00',
			dataIndex : 'sales'
		} ],

		listeners : {
			// Fires when the selected nodes change.
			selectionchange : function(model, records) {
				if (records[0] != undefined) {
					var selectedId = records[0].data.id;
					lineGraphStoreLoad(selectedId);
				}
			}
		}
	});
			
	var gridPanelSearchLogic = function () {
		var o = {
			url : jsonURL,
			method: 'get',
			callback: function (options, success, response) {
				var myJsonObj = Ext.decode(response.responseText);
				if (true !== success) {
					Ext.notification.msg('Failed',response.responseText);
					return;
				}
				try {

					var records_cnt = myJsonObj.totalCount;
					if (records_cnt == 0){
						myJsonObj.items = '';
					}

					loadGridStore();
					lineGraphStoreLoad(0);

				} catch (e) {
					return;
				}
			},
			scope: this,
			params: {
				cmd: 'gridGetData',
				searchText: SR.searchTextField.getValue(),
				fromDate: SR.fromDateField.getValue(),
				toDate: SR.toDateField.getValue(),
				start: 0
			}
		};
		Ext.Ajax.request(o);
	};	
	
	// create a bar series to be at the top of the panel.
	var barChart = Ext.create('Ext.chart.Chart', {
		id : 'barchart',
		flex : 1,
		margin : '10 5 0 0',
		cls: 'bar-chart',
		height : 300,
		width: 150,
		insetPadding: 10,
		shadow : false,
		animate : true,
		resize : false,
		store : lineGraphStore,
		params : {
			fromDate : SR.fromDateField.getValue(),
			toDate : SR.toDateField.getValue(),
			start : 0,
			cmd : 'getData'
		},
		axes : [{
			type : 'Numeric',
			position : 'left',
			fields : [ 'sales' ],
			label : {
				font : '10px Lucida Grande'
			},
			minimum : 0
		}, {
			type : 'Category',
			position : 'bottom',
			label : {
				font : '10px Lucida Grande'
			},
			fields : [ 'period' ]
		} ],
		series : [ {
			type : 'line',
			smooth : true,
			highlight: {
                size: 7,
                radius: 7
            },
			axis : 'left',
			highlight : true,
			style : {
				fill : '#456d9f'
			},
			highlightCfg : {
				fill : '#000'
			},
			markerConfig : {
				color : '#D7E3F2',
				type : 'circle',
				size : 4,
				radius : 2
			},
			tips : {
				trackMouse : true,
				width : 100,
				renderer : function(storeItem, item) {
					var toolTipText = '';
						toolTipText = storeItem.data['sales'] + '<br\> ' + storeItem.data['period'];
					this.setTitle(toolTipText);
				}
			},
			listeners : {
				'itemmouseup' : function(item) {
					// code to select the grid data on click of the graph.
				}
			},
			xField : [ 'period' ],
			yField : [ 'sales' ]
		} ]
	});
	// disable highlighting by default.
	barChart.series.get(0).highlight = true;
		
	var gridForm = Ext.create('Ext.form.Panel', {
		tbar : [ '<b>Sales</b>', 
		
		{ xtype : 'tbspacer' },
		SR.fromDateField,
		{ xtype : 'tbspacer'},
		SR.toDateField,
		{ xtype : 'tbspacer'},

		smartDateComboBox, '',SR.searchTextField,{ icon: imgURL + 'search.png' },
		'->', {
			text : '',
			icon : imgURL + 'refresh.gif',
			tooltip : 'Reload',
			scope : this,
			id : 'reload',
			listeners : {
				click : function() {
					loadGridStore();
				}
			}
		} ],
		height : 400,
		layout : {
			type : 'hbox',
			align : 'stretch'
		},

		items : [ gridPanel, {
			layout : {
				type : 'hbox',
				align : 'stretch'
			},
			flex : 3,
			border : false,
			bodyStyle : 'background-color: transparent',

			items : [ {
				flex : 1,
				layout : {
					type : 'hbox',
					align : 'fit'
				},
				items : [ barChart ]
			} ]
		} ],
		renderTo : 'smart-reporter'
	});
	
	smartDateComboBox.setValue(selectedDateValue);
	if(fileExists == 0){
		SR.fromDateField.setValue(SR.checkFromDate);

		SR.fromDateField.setMinValue(SR.checkFromDate);
		SR.toDateField.setMinValue(SR.checkFromDate);
	}else{
		proSelectDate(selectedDateValue);
	}
	getSales();
});
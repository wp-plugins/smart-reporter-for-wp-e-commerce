Ext.onReady(function() {

	// var itemsPerPage = 25; // set the number of items you want per page
	var selectedStoreItem = false;

	// from-date and to-date textfields
	var fromDateTxt = new Ext.form.TextField({
		emptyText : 'From Date',
		readOnly : true,
		width : 80
	});
	var toDateTxt = new Ext.form.TextField({
		emptyText : 'To Date',
		readOnly : true,
		width : 80
	});
	var now = new Date();
	var initDate = new Date(0);
	var lastMonDate = new Date(now.getFullYear(), now.getMonth() - 1, now
			.getDate() + 1);

	fromDateTxt.setValue(Ext.Date.format(lastMonDate, 'M j Y'));
	toDateTxt.setValue(Ext.Date.format(now, 'M j Y'));

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
			fromDate : fromDateTxt.getValue(),
			toDate : toDateTxt.getValue(),
			start : 0,
			// limit: itemsPerPage,
			cmd : 'getData'
		},
		// pageSize: itemsPerPage, // items per page
		proxy : {
			type : 'ajax',
			url : jsonUrl, // url that will load data with respect to start and
			// limit params
			reader : {
				type : 'json',
				totalProperty : 'totalCount',
				root : 'items'
			}
		}
	});

	lineGraphStore.on('load',
			function() {
				if (lineGraphStore.getTotalCount() == 0) {
					Ext.Msg.alert('Info',
							'No sales found between the selected period');
				} else {
				}

			});

	// store for graph
	var lineGraphStoreLoad = function() {
		lineGraphStore.load({
			params : {
				fromDate : fromDateTxt.getValue(),
				toDate : toDateTxt.getValue(),
				start : 0,
				// limit: itemsPerPage,
				cmd : 'getData'
			}
		});
	};

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
		}, {
			name : 'sales',
			type : 'float'
		}, ],
		// pageSize: itemsPerPage, // items per page
		proxy : {
			type : 'ajax',
			url : jsonUrl, // url that will load data with respect to start and
			// limit params
			reader : {
				type : 'json',
				totalProperty : 'gridTotalCount',
				root : 'gridItems'
			}
		},
		params : {
			fromDate : fromDateTxt.getValue(),
			toDate : toDateTxt.getValue(),
			start : 0,
			// limit: itemsPerPage,
			cmd : 'gridGetData'
		},
		listeners : {
			load : function() {
				var model = gridPanel.getSelectionModel();
				model.select(0);

				if (this.getTotalCount() == 0) {
//					this.item = ;
				} else {
					lineGraphStore.load({
						params : {
							fromDate : fromDateTxt.getValue(),
							toDate : toDateTxt.getValue(),
							start : 0,
							id : 0,
							// limit: itemsPerPage,
							cmd : 'getData'
						}
					});
				}

			}
		}
	});

	// create a grid that will list the dataset items.
	var gridPanel = Ext.create('Ext.grid.Panel', {
		autoScroll : true,
		columnLines : true,
		flex : 2,
		store : gridStore,
		columns : [
		/*
		 * { text : 'Id', width : 150, sortable : true, dataIndex: 'id', },
		 */
		{
			text : 'Products',
			width : 200,
			flex : 1.5,
			sortable : true,
			dataIndex : 'products'
		}, {
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
			selectionchange : function(model, records) {
				if (records[0] != undefined) {
					var selectedId = records[0].data.id;

					lineGraphStore.load({
						params : {
							fromDate : fromDateTxt.getValue(),
							toDate : toDateTxt.getValue(),
							start : 0,
							id : selectedId,
							// limit: itemsPerPage,
							cmd : 'getData'
						}
					});
				}
			}
		}
	});

	var fromDateMenu = new Ext.menu.DatePicker({
		handler : function(dp, date) {
			fromDateTxt.setValue(Ext.Date.format(date, 'M j Y'));
			loadGridStore();

			lineGraphStore.load({
				params : {
					fromDate : fromDateTxt.getValue(),
					toDate : toDateTxt.getValue(),
					start : 0,
					id : 0,
					// limit: itemsPerPage,
					cmd : 'getData'
				}
			});
		},
		maxDate : now
	});

	var toDateMenu = new Ext.menu.DatePicker({
		handler : function(dp, date) {
			toDateTxt.setValue(Ext.Date.format(date, 'M j Y'));
			loadGridStore();

			lineGraphStore.load({
				params : {
					fromDate : fromDateTxt.getValue(),
					toDate : toDateTxt.getValue(),
					start : 0,
					id : 0,
					// limit: itemsPerPage,
					cmd : 'getData'
				}
			});
		},
		maxDate : now
	});

	var loadGridStore = function() {
		gridStore.load({
			params : {
				fromDate : fromDateTxt.getValue(),
				toDate : toDateTxt.getValue(),
				start : 0,
				// limit: itemsPerPage,
				cmd : 'gridGetData'
			}
		});
	};

	// create a bar series to be at the top of the panel.
	var barChart = Ext.create('Ext.chart.Chart', {
		id : 'barchart',
		flex : 1,
		margin : '10 5 0 0',
		cls: 'bar-chart',
		height : 300,
		width: 150,
		// theme: 'Red',
		insetPadding: 10,
		shadow : false,
		animate : true,
		resize : false,
		store : lineGraphStore,
		params : {
			fromDate : fromDateTxt.getValue(),
			toDate : toDateTxt.getValue(),
			start : 0,
			// limit: itemsPerPage,
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
//				'stroke-width' : 1,
//				stroke : 'rgb(215, 227, 242)'
			},
			tips : {
				trackMouse : true,
				width : 100,
				renderer : function(storeItem, item) {
					var toolTipText = '';
					toolTipText = storeItem.data['sales'] + '<br\> '
							+ storeItem.data['period'];
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
		tbar : [ '<b>Sales</b>', {
			xtype : 'tbspacer',
			width : 300
		}, {
			text : 'From:'
		}, fromDateTxt, {
			icon : imgURL + 'calendar.gif',
			menu : fromDateMenu
		}, {
			text : 'To:'
		}, toDateTxt, {
			icon : imgURL + 'calendar.gif',
			menu : toDateMenu
		}, '->', {
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
		// title: 'Company data',
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

	loadGridStore();

});
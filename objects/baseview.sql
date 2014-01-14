if not exists (select * from sysobjects where name='vw_HTGRMSreportBaseview' and xtype='V')
    create view vw_HTGRMSreportBaseview as 
          SELECT
             SAMUnits.SAMUnitName,
             SAMUnits.SAMLocation,
             EMUUnits.EMUUnitName,
             EMUValues.ReadingDate,
             EMUUnits.Location1,
             EMUUnits.Location2,
             EMUUnits.Location3,
             EMUUnits.Location4,
             EMUUnits.Location5,
             EMUValues.KWH1,
             EMUValues.KWH2,
             EMUValues.KWH3,
             EMUValues.KWH4,
             EMUValues.KWH5,
             EMUValues.KWH6,
             EMUUnits.Location6,
             EMUValues.CT1Conf,
             EMUValues.CT2Conf,
             EMUValues.CT3Conf,
             EMUValues.CT4Conf,
             EMUValues.CT5Conf,
             EMUValues.CT6Conf,
             ServiceStates.ServiceStateText
        FROM ((inala_dump.dbo.SAMUnits SAMUnits
               INNER JOIN inala_dump.dbo.ServiceStates ServiceStates
                  ON (SAMUnits.ServiceState = ServiceStates.ServiceStateID))
              INNER JOIN inala_dump.dbo.EMUUnits EMUUnits
                 ON (SAMUnits.SAMID = EMUUnits.SAMID))
             INNER JOIN inala_dump.dbo.EMUValues EMUValues
                ON (EMUUnits.EMUID = EMUValues.EMUID)
       WHERE     (EMUUnits.EMUUnitName IN ('EMU 1', 'EMU 2'))
             AND (ServiceStates.ServiceStateText = 'HTG NOC')
      ORDER BY SAMUnits.SAMUnitName ASC,
               SAMUnits.SAMLocation ASC,
               EMUValues.ReadingDate ASC
go
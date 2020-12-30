

#import "DRCTSGetTableRowsManagedObjectBuilder.h"

/**
* NOTE: This class is auto generated by the swagger code generator program.
* https://github.com/swagger-api/swagger-codegen
* Do not edit the class manually.
*/

@implementation DRCTSGetTableRowsManagedObjectBuilder

-(instancetype)init {
    self = [super init];
    if (self != nil) {
       _metaBuilder = [[DRCTSGetTableRowsMetaManagedObjectBuilder alloc] init];
       _dataBuilder = [[DRCTSGetTableRowsDataManagedObjectBuilder alloc] init];
    }
    return self;
}

-(DRCTSGetTableRowsManagedObject*)createNewDRCTSGetTableRowsManagedObjectInContext:(NSManagedObjectContext*)context {
    DRCTSGetTableRowsManagedObject *managedObject = [NSEntityDescription insertNewObjectForEntityForName:NSStringFromClass([DRCTSGetTableRowsManagedObject class]) inManagedObjectContext:context];
    return managedObject;
}

-(DRCTSGetTableRowsManagedObject*)DRCTSGetTableRowsManagedObjectFromDRCTSGetTableRows:(DRCTSGetTableRows*)object context:(NSManagedObjectContext*)context {
    DRCTSGetTableRowsManagedObject* newDRCTSGetTableRows = [self createNewDRCTSGetTableRowsManagedObjectInContext:context];
    [self updateDRCTSGetTableRowsManagedObject:newDRCTSGetTableRows withDRCTSGetTableRows:object];
    return newDRCTSGetTableRows;
}

-(void)updateDRCTSGetTableRowsManagedObject:(DRCTSGetTableRowsManagedObject*)managedObject withDRCTSGetTableRows:(DRCTSGetTableRows*)object {
    if(!managedObject || !object) {
        return;
    }
    NSManagedObjectContext* context = managedObject.managedObjectContext;

    if(!managedObject.meta) {
        managedObject.meta = [self.metaBuilder DRCTSGetTableRowsMetaManagedObjectFromDRCTSGetTableRowsMeta:object.meta context:managedObject.managedObjectContext];
    } else {
        [self.metaBuilder updateDRCTSGetTableRowsMetaManagedObject:managedObject.meta withDRCTSGetTableRowsMeta:object.meta];
    }
    if(managedObject.data) {
        for (id object in managedObject.data) {
            [context deleteObject:object];
        }
    }
    if(object.data) {
        NSMutableOrderedSet * convertedObjs = [NSMutableOrderedSet orderedSet];
        for (id innerObject in object.data) {
            id convertedObj = [self.dataBuilder DRCTSGetTableRowsDataManagedObjectFromDRCTSGetTableRowsData:innerObject context:managedObject.managedObjectContext];
            [convertedObjs addObject:convertedObj];
        }
        managedObject.data = convertedObjs;
    }

}

-(DRCTSGetTableRows*)DRCTSGetTableRowsFromDRCTSGetTableRowsManagedObject:(DRCTSGetTableRowsManagedObject*)obj {
    if(!obj) {
        return nil;
    }
    DRCTSGetTableRows* newDRCTSGetTableRows = [[DRCTSGetTableRows alloc] init];
    [self updateDRCTSGetTableRows:newDRCTSGetTableRows withDRCTSGetTableRowsManagedObject:obj];
    return newDRCTSGetTableRows;
}

-(void)updateDRCTSGetTableRows:(DRCTSGetTableRows*)newDRCTSGetTableRows withDRCTSGetTableRowsManagedObject:(DRCTSGetTableRowsManagedObject*)obj {
    newDRCTSGetTableRows.meta = [self.metaBuilder DRCTSGetTableRowsMetaFromDRCTSGetTableRowsMetaManagedObject:obj.meta];
    if(obj.data != nil) {
        NSMutableArray* convertedObjs = [NSMutableArray array];
        for (id innerObject in obj.data) {
            id convertedObj = [self.dataBuilder DRCTSGetTableRowsDataFromDRCTSGetTableRowsDataManagedObject:innerObject];
            [convertedObjs addObject:convertedObj];
        }
        newDRCTSGetTableRows.data = (NSArray<DRCTSGetTableRowsData>*)convertedObjs;
    }
    
}

@end
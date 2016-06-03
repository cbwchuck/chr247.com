<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| This route group applies the "web" middleware group to every route
| it contains. The "web" middleware group is defined in your HTTP
| kernel and includes session state, CSRF protection, and more.
|
*/


Route::group(['middleware' => 'web'], function () {
    Route::auth();

    //register clinics
    Route::get('registerClinic', ['as' => 'registerClinic', 'uses' => 'ClinicController@showRegistrationForm']);
    Route::post('registerClinic', ['as' => 'registerClinic', 'uses' => 'ClinicController@postRegister']);

    /*
     * Routes that require to be authenticated
     */
    Route::group(['middleware' => 'auth'], function () {
        /*
         * Dashboard
         * The data required for the dashboard will be returned from this function
         */
        Route::get('/', ['as' => 'root', 'uses' => function () {
            $clinic = \App\Clinic::getCurrentClinic();
            $prescriptions = \App\Prescription::whereIn('patient_id', $clinic->patients()->lists('id'));

            $prescriptionCount = $prescriptions->where('issued', 1)->count();
            $payments = \App\Payment::whereIn('prescription_id',
                $prescriptions->where('issued', 1)->lists('id'))->sum('amount');

            return view('dashboard', ['clinic'   => $clinic, 'prescriptionCount' => $prescriptionCount,
                                      'payments' => $payments]);
        }]);

        // Global Search
        Route::get('search', ['as' => 'search', 'uses' => 'UtilityController@search']);

        // Issue Medicine
        Route::get('issueMedicine', ['as' => 'issueMedicine', 'uses' => 'PrescriptionController@viewIssueMedicine']);

        /*
         * SETTINGS
         */
        Route::group(['prefix' => 'settings'], function () {
            Route::get('/', ['as' => 'settings', 'uses' => 'SettingsController@viewSettings']);
            Route::post('changePassword', ['as' => 'changePassword', 'uses' => 'SettingsController@changePassword']);
            Route::post('createAccount', ['as' => 'createAccount', 'uses' => 'SettingsController@createAccount']);

            // Routes to compensate the get methods of post requests
            Route::get('changePassword', ['uses' => 'SettingsController@viewSettings']);
            Route::get('createAccount', ['uses' => 'SettingsController@viewSettings']);
        });

        /*
         * Queue    :   Routes related to the queue
         */
        Route::group(['prefix' => 'queue'], function () {
            Route::get('/', ['as' => 'queue', 'uses' => 'QueueController@viewQueue']);

            Route::get('addToQueue/{patientId}', ['as' => 'addToQueue', 'uses' => 'QueueController@addToQueue']);
            Route::get('create', ['as' => 'createQueue', 'uses' => 'QueueController@createQueue']);
            Route::get('close', ['as' => 'closeQueue', 'uses' => 'QueueController@closeQueue']);
        });


        /*
         * Routes that manage all the content of patients
         */
        Route::group(['prefix' => 'patients'], function () {
            Route::get('/', ['as' => 'patients', 'uses' => 'PatientController@getPatientList']);

            /*
             * Patients
             */
            Route::post('addPatient', ['as' => 'addPatient', 'uses' => 'PatientController@addPatient']);
            Route::get('patient/{id}', ['as' => 'patient', 'uses' => 'PatientController@getPatient']);
            Route::any('deletePatient/{id}', ['as' => 'deletePatient', 'uses' => 'PatientController@deletePatient']);
            Route::post('editPatient/{id}', ['as' => 'editPatient', 'uses' => 'PatientController@editPatient']);
        });

        /*
         * Routes to manage all the content of drugs
         */
        Route::group(['prefix' => 'drugs'], function () {
            Route::get('/', ['as' => 'drugs', 'uses' => 'DrugController@getDrugList']);

            /*
             * Drugs
             */
            Route::get('drug/{id}', ['as' => 'drug', 'uses' => 'DrugController@getDrug']);
            Route::post('addDrug', ['as' => 'addDrug', 'uses' => 'DrugController@addDrug']);
            Route::post('deleteDrug/{id}', ['as' => 'deleteDrug', 'uses' => 'DrugController@deleteDrug']);
            Route::post('editDrug/{id}', ['as' => 'editDrug', 'uses' => 'DrugController@editDrug']);


            /*
             * Stocks
             */
            Route::post('addStock/{drugId}', ['as' => 'addStock', 'uses' => 'StockController@addStock']);

            /*
             * Drug types
             */
            Route::get('drugTypes', ['as' => 'drugTypes', 'uses' => 'DrugTypeController@getDrugTypeList']);
            Route::post('addDrugType', ['as' => 'addDrugType', 'uses' => 'DrugTypeController@addDrugType']);
            Route::post('deleteDrugType/{id}', ['as' => 'deleteDrugType', 'uses' => 'DrugTypeController@deleteDrugType']);

            /*
             * Dosages
             */
            Route::get('dosages', ['as' => 'dosages', 'uses' => 'DosageController@getDosageList']);
            Route::post('addDosage', ['as' => 'addDosage', 'uses' => 'DosageController@addDosage']);
            Route::post('addFrequency', ['as' => 'addFrequency', 'uses' => 'DosageController@addFrequency']);
            Route::post('addPeriod', ['as' => 'addPeriod', 'uses' => 'DosageController@addPeriod']);

            Route::get('deleteDosage/{id}', ['as' => 'deleteDosage', 'uses' => 'DosageController@deleteDosage']);
            Route::get('deleteFrequency/{id}', ['as' => 'deleteFrequency', 'uses' => 'DosageController@deleteFrequency']);
            Route::get('deletePeriod/{id}', ['as' => 'deletePeriod', 'uses' => 'DosageController@deletePeriod']);
        });


        /*
         * API
         * Routes to manage the internal API for AJAX calls
         */
        Route::group(['prefix' => 'API'], function () {
            Route::post('drugs', 'APIController@getDrugs');
            Route::post('dosages', 'APIController@getDosages');
            Route::post('savePrescription', 'APIController@savePrescription');

            //getting prescriptions
            Route::post('getPrescriptions/{id}', 'APIController@getPrescriptions');
            Route::post('getAllPrescriptions', 'APIController@getAllRemainingPrescriptions');

            Route::post('issuePrescription', 'APIController@issuePrescription');
            Route::post('getPrescriptions/{id}', 'APIController@getPrescriptions');
            Route::post('deletePrescription/{id}', 'APIController@deletePrescription');
            Route::post('getMedicalRecords/{patientId}', 'APIController@getMedicalRecords');

            //queue
            Route::post('getQueue', 'APIController@getQueue');
            Route::post('updateQueue', 'APIController@updateQueue');
        });
    });

    /*
     * SUPPORT API
     */
    Route::group(['prefix' => 'API'], function () {
        //clinic registration support
        Route::post('support/timezones/{countryCode}', 'SupportController@getTimezones');

    });

});


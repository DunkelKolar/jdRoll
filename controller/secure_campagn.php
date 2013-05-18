<?php

	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\Response;

	/*
	    Controller de campagne (sécurisé)
	*/
	$securedCampagneController = $app['controllers_factory'];
	$securedCampagneController->before($mustBeLogged);

	$securedCampagneController->get('/new', function() use($app) {
	    $campagne = $app['campagneService']->getBlankCampagne();
	    return $app->render('campagne_form.html.twig', ['campagne' => $campagne, 'error' => ""]);
	})->bind("campagne_new");

	$securedCampagneController->get('/{id}/edit', function($id) use($app) {
	    $campagne = $app['campagneService']->getCampagne($id);
	    return $app->render('campagne_form.html.twig', ['campagne' => $campagne, 'error' => ""]);
	})->bind("campagne_edit");

	$securedCampagneController->get('/join/{id}', function($id) use($app) {
		try {
		    $campagne = $app['campagneService']->addJoueur($id, $app['session']->get('user')['id']);
		    return $app->redirect($app->path('campagne_my_list'));
		} catch (Exception $e) {
			$campagnes = $app['campagneService']->getAllCampagne();
    		return $app->render('campagne_list.html.twig', ['campagnes' => $campagnes, 'error' => $e->getMessage()]);
		}
	})->bind("campagne_join");

	$securedCampagneController->get('/quit/{id}', function($id) use($app) {
		try {
		    $campagne = $app['campagneService']->removeJoueur($id, $app['session']->get('user')['id']);
		    return $app->redirect($app->path('campagne_my_list'));
		} catch (Exception $e) {
			$campagnes = $app['campagneService']->getAllCampagne();
    		return $app->render('campagne_list.html.twig', ['campagnes' => $campagnes, 'error' => $e->getMessage()]);
		}
	})->bind("campagne_quit");

	$securedCampagneController->get('/my_list', function() use($app) {
	    $campagnes = $app['campagneService']->getMyCampagnes();
	    $campagnesPj = $app['campagneService']->getMyActivePjCampagnes();
	    return $app->render('campagne_my_list.html.twig', ['campagnes' => $campagnes, 'campagnes_pj' => $campagnesPj, 'error' => ""]);
	})->bind("campagne_my_list");

	$securedCampagneController->post('/save', function(Request $request) use($app) {
	    try {
	        if ($request->get('id') == '') {
	            $app['campagneService']->createCampagne($request);
	            return $app->redirect($app->path('homepage'));
	        } else {
	            $app['campagneService']->updateCampagne($request);
	            return $app->redirect($app->path('homepage'));
	        }
	    } catch (Exception $e) {
	        $campagne = $app['campagneService']->getFormCampagne($request);
	        return $app->render('campagne_form.html.twig', ['campagne' => $campagne, 'error' => $e->getMessage()]);    
	    }
	})->bind("campagne_save");
	
	$securedCampagneController->get('/sidebar/{campagne_id}', function(Request $request, $campagne_id) use($app) {
		$player_id = $app['session']->get('user')['id'];
		$perso = $app['persoService']->getPersonnage($campagne_id, $player_id);
		$allPerso = $app['persoService']->getPersonnagesInCampagne($campagne_id);
		return $app->render('sidebar_campagne.html.twig', ['campagne_id' => $campagne_id, 'perso' => $perso, 'allPerso' => $allPerso]);
	})->bind("sidebar_campagne");
	
	$app->mount('/campagne', $securedCampagneController);

?>
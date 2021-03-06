<?php
/**
 * Control feedback module
 *
 * @package chat
 * @copyright (C) 2013 jdRoll
 * @license MIT
 */


	use Symfony\Component\HttpFoundation\Request;
	use Symfony\Component\HttpFoundation\JsonResponse;

	$feedbackController = $app['controllers_factory'];

	$feedbackController->post('/', function(Request $request) use($app) {
        $user = $app['session']->get('user');
        $feedback = json_decode($request->getContent());
        $title = $feedback->title;
        $content = $feedback->content;
        if(($title == "") || ($content == "")) {
            return new JsonResponse('Merci de saisir un titre et un contenu', 400);
        } else {
		    $feedback = $app['feedbackService']->create($user, $title, $content);
            return new JsonResponse($feedback, 201);
        }
	})->before($mustBeLogged);

    $feedbackController->get('/{id}', function($id) use($app) {
        $user = $app['session']->get('user');
        $feedback = $app['feedbackService']->get($id, $user);
        $comments = $app['feedbackService']->getComments($id);
        $isAdmin = $app["campagneService"]->IsAdmin();
        return $app->render('feedback/detail.html.twig', [
            'feedback' => $feedback,
            'comments' => $comments,
            'is_admin' => $isAdmin,
            'error' => ""]);
	})->bind("feedback_get")->before($mustBeLogged);

    $feedbackController->delete('/{id}', function($id) use($app) {
        $user = $app['session']->get('user');
        $feedback = $app['feedbackService']->delete($id, $user);
        return new JsonResponse($feedback, 200);
    })->bind("feedback_delete")->before($mustBeLogged);

    $feedbackController->post('/{id}/vote', function(Request $request, $id) use($app) {
        $score = $request->get('score');
        $user = $app['session']->get('user');
        $app['monolog']->addInfo('Vote : ' . $score);
        if ($score >= '0') {
            $feedback = $app['feedbackService']->voteUp($id, $user);
        } else {
            $feedback = $app['feedbackService']->voteDown($id, $user);
        }
        return new JsonResponse($feedback, 200);
    })->bind("feedback_vote")->before($mustBeLogged);

    $feedbackController->post('/{id}/comment', function(Request $request, $id) use($app) {
        $content = $request->get('content');
        $user = $app['session']->get('user');
        $feedback = $app['feedbackService']->createComments($id, $user, $content);
        return $app->redirect($app->path('feedback_get', array('id' => $id)));
    })->bind("feedback_comment")->before($mustBeLogged);

    $feedbackController->get('/', function() use($app) {
        $user = $app['session']->get('user');
		$feedbacks = $app['feedbackService']->getOpenFeedbacks($user);
        $lastFeedbacks = $app['feedbackService']->getLastFeedbacks();
        $lastComments = $app['feedbackService']->getLastComments();
        $isAdmin = $app["campagneService"]->IsAdmin();
        return $app->render('feedback/list.html.twig', [
            'feedbacks' => $feedbacks,
            'last_feedbacks' => $lastFeedbacks,
            'last_comments' => $lastComments,
            'is_admin' => $isAdmin,
            'error' => ""]);
	})->bind("feedback_list")->before($mustBeLogged);

	$app->mount('/feedback', $feedbackController);

?>

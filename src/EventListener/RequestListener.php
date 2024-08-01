<?php
namespace App\EventListener;

use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    public function onKernelRequest(RequestEvent $event)
    {
        $request = $event->getRequest();

        // Vérifiez si la route correspond à celle que vous souhaitez modifier
        if ($request->attributes->get('_route') === 'list_movements') {
            $sort = $request->query->get('sort');
            if ($sort && strpos($sort, 'm.') !== 0) {
                $request->query->set('sort', 'm.' . $sort);
            }
        }
    }
}

<?php

namespace App\Controller;

use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;

class GetNbPostController
{
 public function __construct(private PostRepository $rep) {
 }
 public function __invoke(Request $request):int {
    $onLineRequest=$request->get('onLine');
    $conditions=[];
    if($onLineRequest!=null){
        $conditions=['onLine'=>$onLineRequest==='1' ? true: false];
    }
    return $this->rep->count($conditions) ;

 }
}

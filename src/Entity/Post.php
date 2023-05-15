<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\PostRepository;
use App\Controller\GetNbPostController;
use App\Controller\PostPublishController;
use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints\Length;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;

#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ApiResource(
    paginationPartial: true, 
    paginationViaCursor: [
        ['field' => 'id', 'direction' => 'DESC']
    ],
    normalizationContext: [
        'groups'=>['read:collection'],
        'openapi_definition_name'=>'Collection'
],
    denormalizationContext:['groups'=>['put:Post']],
    //permet au client de piloter les choses 
    //càd le client peut preciser le nbre de page qu'il veut
    paginationClientItemsPerPage:true,
    paginationItemsPerPage:3,
    paginationMaximumItemsPerPage:5,
    //les endpoints 
    //Collection operations act on a collection of resources
    collectionOperations:[
        'get',
        'post'=>['validation_groups'=>[Post::class,'validationGroups']],//validationGroups is an static methode see more below
        'nbPosts'=>[
            'method'=>'GET',
            'path'=>'/posts/nbpost',
            'controller'=>GetNbPostController::class,
            'read'=>false,
            'pagination_enabled'=>false,
            'filters'=>[],
            'openapi_context'=>[
                'summary'=>"Récupération du nombre total d'article",
                'parameters'=>[
                   [ 'in'=>'query',
                   'name'=>'onLine',
                   'shema'=>[
                       'type'=>'integer',
                       'minimum'=>0,
                       'maximum'=>1
                   ],
                   'description'=>'Article publier en ligne'
                   ]
                ],
                'responses'=>[
                    '200'=>[
                        'description'=>'ok',
                        'content'=>[
                            'application/json'=>[
                                'shema'=>[
                                    'type'=>'integer',
                                    'example'=>3
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ]
    ],
    //Item operations act on an individual resource
    itemOperations:[
        'put',
        'delete',
        'get'=>['normalization_context'=>[
            'groups'=>['read:collection','read:item','read:Post'],
            'openapi_definition_name'=>'Detail'

            ]
        ],
        'publish'=>[
            'method'=>'POST',
            'path'=>'/posts/{id}/publish',
            'controller'=>PostPublishController::class,
            'openapi_context'=>[
                'summary'=>'Permet de publier un article',
                'requestBody'=>[
                'content'=>[
                    'application/json'=>[  'schema'  => [],
                    'example' => [],
                    ]
                ]
                ]
            ]
        ],
    ],
)]
    #[ApiFilter(SearchFilter::class,properties:['id'=>'exact','title'=>'partial'])]//passe exactement l'id du prod qu'il veut  filtrer
    #[ApiFilter(OrderFilter::class,properties:['id'=>"DESC"])]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]    
    #[Groups(['read:collection'])]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Groups(['read:collection','put:Post']),
      Length(min:5,groups:['create:Post'])
    ]
    private ?string $title = null;

 
    #[ORM\Column(length: 255)]
    #[Groups(['read:collection','put:Post'])]
    private ?string $slug = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Groups(['read:item','put:Post'])]
    private ?string $content = null;

    #[ORM\Column]
    #[Groups(['read:item'])]
    private ?\DateTime $createdAt = null;

    #[ORM\Column]
    private ?\DateTime $updatedAt = null;

    #[ORM\ManyToOne(inversedBy: 'posts',cascade:['persist'])]
    #[Groups(['read:item','put:Post'])]
    private ?Category $category = null;

    #[ORM\Column(type:"boolean",options:["default"=>0])]
    #[Groups(['read:collection',])]
    #[ApiProperty(
        openapiContext: [
            'type' => 'boolean',
            'description' => 'En ligne ou pas'
        ]
    )]
    private ?bool $onLine = false;


    public function __construct()
    {
        $this->createdAt=new \DateTime();
        $this->updatedAt=new \DateTime();
    }
    /**
     * permet de creer une collection de groups de normaliser
     *
     * @param Post $post
     * @return array
     */
    public static function validationGroups(Post $post):array
    {
        return ['create:Post'];
    }
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getCreatedAt(): ?\DateTime
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTime $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTime
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;

        return $this;
    }

    public function isOnLine(): ?bool
    {
        return $this->onLine;
    }

    public function setOnLine(bool $onLine): self
    {
        $this->onLine = $onLine;

        return $this;
    }
}

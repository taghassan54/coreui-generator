<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Repositories\PostRepository;
use App\Repositories\BlogCategoryRepository;
use App\Models\BlogCategory;
use App\Http\Controllers\AppBaseController;
use Illuminate\Http\Request;
use Flash;
use Response;

class PostController extends AppBaseController
{
    /** @var  PostRepository */
    private $postRepository;
    private $categories;

    public function __construct(PostRepository $postRepo,postRepository $categoriesRepo )
    {
        $this->middleware('can:Blogs content')->except('singlepost');
        $this->postRepository = $postRepo;
        $this->categories = $categoriesRepo;

    }

    /**
     * Display a listing of the Post.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $posts = $this->postRepository->paginate(15);
        $categories= $this->categories->all();
         return view('posts.index')
            ->with('posts', $posts);
    }

    /**
     * Show the form for creating a new Post.
     *
     * @return Response
     */
    public function create()
    {
        $categories= BlogCategory::all();

        return view('posts.create',compact('categories'));
    }

    /**
     * Store a newly created Post in storage.
     *
     * @param CreatePostRequest $request
     *
     * @return Response
     */
    public function store(CreatePostRequest $request)
    {
        $input = $request->all();

        $input['auther_id']=@auth()->user()->id;

        $post = $this->postRepository->create($input);
        if($request->hasFile('file')){
            $files=$request->file;
            foreach ($files as $file) {
                $post->addMedia($file)
                ->toMediaCollection();
            }
        }


        Flash::success('Post saved successfully.');

        return redirect(route('posts.index'));
    }

    /**
     * Display the specified Post.
     *
     * @param int $id
     *
     * @return Response
     */
    public function show($id)
    {
        $post = $this->postRepository->find($id);

        if (empty($post)) {
            Flash::error('Post not found');

            return redirect(route('posts.index'));
        }

        return view('posts.show')->with('post', $post);
    }
    public function singlepost($id)
    {

        $post = $this->postRepository->find(decrypt($id));

        if (empty($post)) {
            Flash::error('Post not found');

            return redirect(route('posts.index'));
        }

        return view('singlepost')->with('post', $post);
    }

    /**
     * Show the form for editing the specified Post.
     *
     * @param int $id
     *
     * @return Response
     */
    public function edit($id)
    {
        $post = $this->postRepository->find($id);
        $categories= BlogCategory::all();
        if (empty($post)) {
            Flash::error('Post not found');

            return redirect(route('posts.index'));
        }

        return view('posts.edit',compact('categories'))->with('post', $post);
    }

    /**
     * Update the specified Post in storage.
     *
     * @param int $id
     * @param UpdatePostRequest $request
     *
     * @return Response
     */
    public function update($id, UpdatePostRequest $request)
    {
        $post = $this->postRepository->find($id);

        if (empty($post)) {
            Flash::error('Post not found');

            return redirect(route('posts.index'));
        }
        if($request->hasFile('file')){
            $files=$request->file;
            foreach ($files as $file) {
                $post->addMedia($file)
                ->toMediaCollection();
            }
        }
        $post = $this->postRepository->update($request->all(), $id);

        Flash::success('Post updated successfully.');

        return redirect(route('posts.index'));
    }

    /**
     * Remove the specified Post from storage.
     *
     * @param int $id
     *
     * @throws \Exception
     *
     * @return Response
     */
    public function destroy($id)
    {
        $post = $this->postRepository->find($id);

        if (empty($post)) {
            Flash::error('Post not found');

            return redirect(route('posts.index'));
        }

        $this->postRepository->delete($id);

        Flash::success('Post deleted successfully.');

        return redirect(route('posts.index'));
    }
}

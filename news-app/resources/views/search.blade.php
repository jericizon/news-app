<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <title>News App</title>       
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
        <link href="{{ url('/css/bootstrap.min.css') }}" rel="stylesheet" />
        <link href="{{ url('/css/style.css') }}" rel="stylesheet" />
        <script src="{{ url('/js/vue.global.js') }}"></script>

        <style>
            #main-container{
                margin-top: 140px;
            }
            /* Define styles for the SVG */
            svg.pin-icon {
                cursor: pointer;
                width: 20px; /* Set the width of the SVG */
                height: 20px; /* Set the height of the SVG */
                transition: fill 0.3s ease; /* Add a smooth transition effect */
                fill: #cacaca;
            }
            /* Change fill color on hover */
            svg.pin-icon:hover {
                fill: red; /* Change the fill color to red on hover */
            }
            .spinner-border .sr-only {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

        </style>
    </head>
    <body>
        <div id="app">
            <div class="fixed-top">
                <nav class="navbar navbar-expand-lg bg-body-tertiary">
                    <div class="container">                    
                        <form class="d-flex col-10 d-flex py-3" role="search" @submit.prevent="fetchData">
                            <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search" v-model="searchKey">
                            <div>
                                
                            </div>
                            <button class="btn btn-outline-primary" type="submit">
                                <div class="spinner-border text-primary" role="status" v-if="formLoading || showSpinner">
                                    <span class="sr-only">Loading...</span>
                                </div>
                                <span v-else>Search</span>
                            </button>                       
                        </form>
                    </div>
                </nav>
            </div>

            <div id="main-container" class="container">                

                <div class="row">
                    <div class="col-lg-12">
                        <div v-if="responseData !== null && hasSearchResult" class="wrapper wrapper-content">
                            <div class="ibox-content forum-container" v-for="(news, category) in responseData.results" :key="`news-${news.id}`">
                                <div class="forum-title">
                                    <h3>@{{ category }}</h3>
                                </div>
                                <div class="forum-item" v-for="(article, index) in news" :key="`article-${article.id}`">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="forum-icon">
                                                <svg class="float-end pin-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-star-fill" viewBox="0 0 16 16" @click="pinArticle(article)">
                                                    <path d="M3.612 15.443c-.386.198-.824-.149-.746-.592l.83-4.73L.173 6.765c-.329-.314-.158-.888.283-.95l4.898-.696L7.538.792c.197-.39.73-.39.927 0l2.184 4.327 4.898.696c.441.062.612.636.282.95l-3.522 3.356.83 4.73c.078.443-.36.79-.746.592L8 13.187l-4.389 2.256z" />
                                                </svg>
                                            </div>
                                            <div>
                                                <a :href="article.url" class="forum-item-title">@{{ article.title }}</a>
                                                <div class="forum-sub-title">@{{ article.date }}</div>
                                            </div>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else class="wrapper wrapper-content">
                            <div class="ibox-content forum-container">
                                <div class="forum-item">
                                    <div class="row">
                                        <div class="col-12">
                                            <p>No news available. Use the search box to find your news interests.</p>
                                        </div>                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <nav class="my-5" v-if="totalPages > 1">
                    <ul class="align-content-center flex justify-content-center pagination">
                        <li class="page-item">
                            <a class="page-link" href="#" @click="changePage(currentPage - 1)"                            
                            >Prev</a>
                        </li>
                        <li class="page-item" v-for="page in paginationArray" :key="page">
                            <a class="page-link" href="#" @click="changePage(page)"
                            :class="{active: page === currentPage}"
                            >@{{ page }}</a>
                        </li>
                        <li class="page-item">
                            <a class="page-link" href="#" @click="changePage(currentPage + 1)"                            
                            >Next</a>
                        </li>
                    </ul>
                </nav>  

                <div class="mt-5" v-if="pinnedArticles !== null && pinnedArticles.results">         
                    <h3>Pinned articles</h3>
                    <div class="row">
                        <div class="col-lg-12">
                            <div class="wrapper wrapper-content">
                                <div class="ibox-content forum-container bg-warning-subtle" v-for="(news, category) in pinnedArticles.results" :key="`pinned-news-${news.id}`">
                                    <div class="forum-title">
                                        <h3>@{{ category }}</h3>
                                    </div>
                                    <div class="forum-item" v-for="(article, index) in news" :key="`article-${article.id}`">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="forum-icon">
                                                    <svg class="float-end pin-icon"xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16"  @click="unPinArticle(article.id)">
                                                        <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5"/>
                                                    </svg>
                                                </div>
                                                <div>
                                                    <a :href="article.url" class="forum-item-title">@{{ article.title }}</a>
                                                    <div class="forum-sub-title">@{{ article.date }}</div>
                                                </div>
                                            </div>                                        
                                        </div>
                                    </div>
                                </div>
                            </div>                            
                        </div>
                    </div>
                </div>                
            </div>
        </div>        

        <script>

        let debounceRequest;
        const { createApp, ref, reactive, computed, watch } = Vue

        createApp({
            setup() {
                const responseData = ref(null);
                const pinnedArticles = ref(null);
                const formLoading = ref(false);
                const searchKey = ref('');
                const currentPage = ref(1);
                const pagesToShow = ref(8);
                const showSpinner = ref(false);

                const totalPages = computed(() => {
                    return Math.ceil(responseData.value?.total / responseData.value?.pageSize);
                });

                const hasSearchResult = computed(() => {
                    return responseData.value?.startIndex >= 1
                })

                const startPage = computed(() => {
                    let start = currentPage.value - Math.floor(pagesToShow.value / 2);
                    start = Math.max(start, 1);
                    start = Math.min(start, responseData.value?.pages - pagesToShow.value + 1);
                    return start;
                });
                const endPage = computed(() => {
                    return Math.min(startPage.value + pagesToShow.value - 1, totalPages.value);
                });
                
                const paginationArray = computed(() => {
                    const array = [];
                    for (let i = startPage.value; i <= endPage.value; i++) {
                        array.push(i);
                    }
                    return array;
                });                
                
                const fetchPinnedArticles = async () => {
                    try {
                        const response = await fetch('{{ url('/api/get-pinned-articles') }}');
                        const pinnedData = await response.json();

                        if(pinnedData.results.length === 0) {
                            pinnedArticles.value = null;
                        } else {
                            pinnedArticles.value = pinnedData
                        }

                    } catch (error) {
                        console.error('Error fetching data:', error);
                    } finally {
                        
                    }
                }

                const changePage = async (pageNumber) => {
                    if(pageNumber <= 0) {
                        pageNumber = 1;
                    }

                    if(pageNumber > endPage) {
                        pageNumber = endPage
                    }
                    currentPage.value = pageNumber
                    await fetchData();
                }
                
                const fetchData = async () => {
                    if(formLoading.value === true) {
                        return;
                    }
                    formLoading.value = true;
                    try {
                        const response = await fetch('{{ url('/api/news-app') }}', {
                            method: 'POST',            
                            headers: {
                              'Content-Type': 'application/json'
                            },                
                            body: JSON.stringify({
                                search: searchKey.value,
                                page: currentPage.value
                            })
                        });                                 
                        const data = await response.json();
                        responseData.value = data                        
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    } finally {
                        formLoading.value = false;
                    }
                };
                
                const pinArticle = async (article) => {
                    if(formLoading.value === true) {
                        return;
                    }
                    try {
                        const response = await fetch('{{ url('/api/pin-article') }}', {
                            method: 'POST',            
                            headers: {
                              'Content-Type': 'application/json'
                            },                
                            body: JSON.stringify({
                                'article_id': article.id,
                                'url': article.url,
                                'date': article.date,
                                'title': article.title,
                                'section_id': article.sectionId,
                                'section_name': article.sectionName,
                            })
                        });
                        
                        fetchPinnedArticles();

                    } catch (error) {
                        console.error('Error fetching data:', error);
                    } finally {
                    }
                };      

                const unPinArticle = async (id) => {
                    try {
                        const response = await fetch(`{{ url('/api/unpin-article') }}/${id}`, {
                            method: 'DELETE',            
                            headers: {
                              'Content-Type': 'application/json'
                            }
                        });                            
                        fetchPinnedArticles();
                    } catch (error) {
                        console.error('Error fetching data:', error);
                    } finally {
                    }
                };                

                watch(searchKey, (newValue, oldValue) => {
                    showSpinner.value = true;
                    clearTimeout(debounceRequest);
                    debounceRequest = setTimeout(() => {
                        fetchData();
                        showSpinner.value = false;
                    }, 500)                    
                });

                fetchPinnedArticles();

                return {                    
                    responseData,
                    pinnedArticles,
                    formLoading,
                    searchKey,
                    paginationArray,
                    currentPage,
                    showSpinner,
                    totalPages,
                    hasSearchResult,

                    fetchPinnedArticles,
                    fetchData,
                    pinArticle,
                    unPinArticle,
                    changePage,
                }
            }
        }).mount('#app')
        </script>
    </body>
</html>

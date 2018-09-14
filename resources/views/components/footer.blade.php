<footer>

    <section class="footer-alert">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    This website is not the official <a href="http://gamescom.global" title="The official gamescom website in english">gamescom website</a>. This is a fanpage to provide some more information regarding the convention.
                </div>
            </div>
        </div>
    </section>

    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h3>Quick links</h3>
                <ul>
                    <li><a href="{{url('gamescom-2019')}}">About gamescom 2018</a></li>
                    <li><a href="{{url('tickets')}}">Tickets</a></li>
                    <li><a href="{{url('sitemap')}}">Sitemap</a></li>
                    @if(Auth::check())
                    <li>
                        <a href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                                         document.getElementById('logout-footer-form').submit();">
                            Logout
                        </a>

                        <form id="logout-footer-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                            {{ csrf_field() }}
                        </form>
                    </li>
                    @else
                    <li><a href="{{url('login')}}">Login</a></li>
                    @endif
                </ul>
            </div>
            <div class="col-md-4">
                <h3>Recently added news</h3>
                @foreach($recent_articles as $article)
                    <a href="{{ url( "article/" . $article['slug']) }}">{{$article['title']}}</a> <br>
                @endforeach
            </div>
            <div class="col-md-4">
                <h3>Open at</h3>
                <strong>Wed. 21 August: </strong><span>10:00 - 20:00</span><br>
                <strong>Thu. 22 August: </strong><span>10:00 - 20:00</span><br>
                <strong>Fri. 23 August: </strong><span>09:00 - 20:00</span><br>
                <strong>Sat. 24 August: </strong><span>09:00 - 20:00</span><br>

                <div class="social-footer">
                    <a href="#"><i class="fa fa-facebook"></i></a>
                    <a target="_blank" href="https://twitter.com/GCEredaction"><i class="fa fa-twitter"></i></a>
                    <a target="_blank" href="https://www.instagram.com/GCERedaction/"><i class="fa fa-instagram"></i></a>
                    <a href="#"><i class="fa fa-linkedin"></i></a>
                    <a href="#"><i class="fa fa-google-plus"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>
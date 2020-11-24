<header>
    @empty(!$config['top_tip'])
        <div class="top-tip">
            <marquee class="top-tip-css" behavior="scroll" scrolldelay="170">
                {{$config['top_tip']}}
            </marquee>
        </div>
    @endempty
    <div class="m_head clearfix">
        <div class="slider">
            <ul>
                @foreach($sliderImg as $val)
                    <li><a href="javascript:;"><img src="{{$val}}" alt=""></a></li>
                @endforeach
            </ul>
        </div>
        <div class="num_box">
            @empty($myItemID)
                @if($config['s_time'] < date('Y-m-d H:i:s') && $config['e_time'] > date('Y-m-d H:i:s'))
                    <a href="{{$urlArr['signUpUrl']}}" class="join_us">我要报名</a>
                @endif
            @else
                <a href="{{$urlArr['detailsUrl'].'&t_id='.$myItemID}}" class="join_us">我的参赛</a>
            @endempty
            <ul class="num_box_ul">
                <li>
                    <span class="text">已报名</span>
                    <span>{{$groupData['item_n']}}</span>
                </li>
                <li>
                    <span class="text">投票人次</span>
                    <span>{{$groupData['voting_n']}}</span>
                </li>
                <li>
                    <span class="text">浏览量</span>
                    <span>{{$groupData['view_n']}}</span>
                </li>
            </ul>
            <div class="countDown">
                距离 <span id="countDownText">活动开始</span> 还剩:
                <strong id="DD">x</strong> 天
                <strong id="HH">x</strong> 时
                <strong id="MM">x</strong> 分
                <strong id="SS">x</strong> 秒
            </div>
            <img src="{{$templatePath.'/img/mw_004.jpg'}}"/>
        </div>
        <div class="search">
            <div class="search_con" style="margin: auto;">
                <div class="btn"><input type="submit" id="searchBtn" value="搜索"></div>
                <div class="text_box">
                    <input type="search" id="searchText" value="{{request()->input('searchKey')}}"
                           placeholder="请输入选手姓名或编号" autocomplete="off">
                </div>
            </div>
        </div>
    </div>
</header>

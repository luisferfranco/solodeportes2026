@props(['url'])
<tr>
<td class="header">
<a href="{{ config('app.url') }}" style="display: inline-block;">
<img src="{{ asset('img/solodeportes.png') }}" alt="SoloDeportes.mx">
<p>{{  $slot }}</p>
</a>
</td>
</tr>

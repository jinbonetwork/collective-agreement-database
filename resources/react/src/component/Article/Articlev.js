import React, { Component } from 'react';
import ReactDOM from 'react-dom';
import axios from 'axios';
import { Link } from 'react-router';

export default class Articlev extends Component {
  constructor(props) {
    super(props);
    this.state = {
      article: props.article,
    };
  }

  render() {
    if(this.state.article.nid) {
      return (
        <div key={`article-overlay-${this.state.article.nid}`} className="overlay">
          <div className="article-info-container">
            <div className="article-info-box">
              <div className="document">
                <h1 dangerouslySetInnerHTML={{ __html: this.state.article.subject }} />
                <div
                  dangerouslySetInnerHTML={{ __html: this.state.article.content }}
                />
              </div>
              <i className="close fa fa-close" onClick={this.onArticleClose.bind(this)}></i>
            </div>
          </div>
          <div className="article-background" onClick={this.onArticleClose.bind(this)}></div>
        </div>
      );
    } else {
      return <div className="hidden" />
    }
  }

  componentDidMount() {
    var self = this;
	jQuery(window).bind('resize.articlev', function(e) {
      self.handleResize();
    });
	this.overlayResize();
	var b = jQuery(ReactDOM.findDOMNode(this)).find('.article-background');
    if(b.length > 0) {
      jQuery(window).bind('keydown.article',function(event) {
        var code = event.charCode || event.keyCode;
        if(code == 27) {
          self.onArticleClose();
        }
      });
    }
    this.scrollToKeyword();
  }

  componentDidUpdate() {
    this.overlayResize();
    this.scrollToKeyword();
  }

  componentWillUnmount() {
    jQuery(window).unbind('resize.article');
    jQuery(window).unbind('keydown.article');
  }

  handleResize() {
    this.overlayResize();
  }

  overlayResize() {
    var winWidth = window.innerWidth;
    var winHeight = window.innerHeight;
    var obj = jQuery(ReactDOM.findDOMNode(this)).find('.article-info-container');
    var w = Math.min(600, parseInt( winWidth * 0.9 ) );
    var max_h = parseInt( winHeight * 0.9 );
    obj.css({
      'width' : w+'px',
      'max-height' : max_h+'px',
      'left' : parseInt( ( winWidth - w ) / 2 )+'px',
      'top' : parseInt( ( winHeight - max_h ) / 2)+'px'
    });
  }

  scrollToKeyword() {
    var $this = jQuery(ReactDOM.findDOMNode(this));
    var k = $this.find('.cadb-keyword:first');
    if(k.length > 0) {
      var p = k.parents('p, h1');
      if(p.length > 0) {
        $this.find('.article-info-container').scrollTo(p,500,{offset: -50});
      }
    }
  }

  onArticleClose() {
    ReactDOM.unmountComponentAtNode(document.getElementById('overlay-container'));
  }
}

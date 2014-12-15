<?php

Interface iSocket {
	public function set_fd($handle);
	public function get_fd();
	public function read();
	public function get_buffer_read();
	public function clear_buffer_read();
	public function get_buffer_write();
	public function clear_buffer_write();
	public function send();
	public function write($data);
}
Class Socket implements iSocket {
	private $fd;
	private $buffer_read = '';
	private $buffer_write = '';
	private $buffer_read_bytes = 0;
	private $buffer_write_bytes = 0;
	private $meta = array();
	public function set_fd($handle) {
		$this->fd = $handle;
	}
	public function get_fd() {
		return $this->fd;
	}
	public function read() {
		// reads a line of data if it can and adds to buffer
		// adds to bytes size also
		// MSG_DONTWAIT
		$buf = '';
		$bytes = @socket_recv($this->fd, $buf, 2048, 0);
		if($bytes === false) {
			if (class_exists('Log')) Log::stdwarn( 'read() We have an error' );
			return false;
		} else {
			if($bytes>0) {
				$this->buffer_read .= $buf;
				$this->buffer_read_bytes += $bytes;
				return $bytes;
			}
		}
	}
	public function send() {
		// int socket_send ( resource $socket , string $buf , int $len , int $flags )
		// $bytes = socket_send( $this->fd, $this->buffer_write, $this->buffer_write_bytes , NULL);
		$bytes = socket_write($this->fd, $this->buffer_write, $this->buffer_write_bytes);

		if ($bytes !== false) {
			//echo "attempted to send buffer\n";
			if($bytes == $this->buffer_write_bytes) {
				$this->clear_buffer_write();
				//echo "sent full buffer apparently\n";
			}
		}else {
			// decide how to handle incomplete data sending
			//echo "unable to send full buffer\n";
		}
		return $bytes;
	}
	public function write($data) {
		// will fill the write buffer for this socket
		// per iteration of the main server loop
		// the buffer from this will be pushed to the clients
		// however if a client fails to collect entire buffer
		// the buffer is truncated accordingly
		$this->buffer_write .= $data;
		$this->buffer_write_bytes += strlen($data);
		return $this->buffer_write_bytes;
	}
	public function get_buffer_read() {
		return $this->buffer_read;
	}
	public function clear_buffer_read() {
		$this->buffer_read = '';
		$this->buffer_read_bytes = 0;
	}
	// I'm not sure why obtaining the write buffer is useful
	public function get_buffer_write() {
		return $this->buffer_write;
	}
	// will probably need a -clear buffer write partial method
	// to preserve unsent data until the next attempt at sending all of the buffer.
	public function clear_buffer_write() {
		$this->buffer_write = '';
		$this->buffer_write_bytes = 0;
	}
}
